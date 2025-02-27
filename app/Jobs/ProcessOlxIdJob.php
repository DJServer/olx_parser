<?php

namespace App\Jobs;

use App\Models\Announcement;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOlxIdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $announcement;

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function handle(): void
    {
        $client = new Client([
            'timeout' => 5,
            'headers' => ['User-Agent' => 'Mozilla/5.0']
        ]);

        try {
            $response = $client->get($this->announcement->url);

            if ($response->getStatusCode() !== 200) {
                return;
            }

            $html = $response->getBody()->getContents();
            preg_match('/"sku":"(\d+)"/', $html, $matches);
            $olxId = $matches[1] ?? null;

            if ($olxId) {
                $this->announcement->update(['olx_id' => $olxId]);
            }
        } catch (\Exception $e) {
            Log::error("Ошибка получения OLX ID: " . $e->getMessage());
        }
    }
}
