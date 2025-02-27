<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\AnnouncementPrice;
use App\Models\Subscription;
use GuzzleHttp\Client;
use App\Services\MailgunService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOlxDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailgun;

    public function __construct()
    {
        $this->mailgun = new MailgunService();
    }

    public function handle()
    {
        $client = new Client();
        $announcements = Announcement::whereNotNull('olx_id')->get();

        foreach ($announcements as $announcement) {
            try {
                $response = $client->get("https://m.olx.ua/api/v2/offers/{$announcement->olx_id}/", [
                    'headers' => ['User-Agent' => 'Mozilla/5.0']
                ]);

                if ($response->getStatusCode() !== 200) {
                    continue;
                }

                $data = json_decode($response->getBody()->getContents(), true)['data'];

                $title = $data['title'] ?? 'Без названия';
                $last_refresh_time = $data['last_refresh_time'] ?? null;

                $priceData = collect($data['params'])->firstWhere('key', 'price')['value'] ?? null;
                $price = $priceData['value'] ?? null;
                $currency = $priceData['currency'] ?? 'UAH';

                // Проверяем, изменилась ли цена
                $lastPrice = $announcement->prices()->latest()->first();
                if ($price && (!$lastPrice || $lastPrice->price != $price)) {
                    $newPrice = AnnouncementPrice::create([
                        'announcement_id' => $announcement->id,
                        'price' => $price,
                        'currency' => $currency,
                    ]);

                    // Отправляем уведомления всем подписчикам
                    $subscribers = Subscription::where('announcement_id', $announcement->id)->pluck('user_id');
                    foreach ($subscribers as $userId) {
                        $user = \App\Models\User::find($userId);
                        if ($user && $user->email) {
                            $subject = "Изменение цены: {$announcement->title}";
                            $message = "Цена на объявление '{$announcement->title}' изменилась.\n\n"
                                . "Новая цена: {$newPrice->price} {$newPrice->currency}\n"
                                . "Ссылка: {$announcement->url}";

                            $this->mailgun->sendEmail($user->email, $subject, $message);
                        }
                    }
                }

                // Обновляем данные объявления
                $announcement->update([
                    'title' => $title,
                    'last_refresh_time' => $last_refresh_time,
                ]);
            } catch (\Exception $e) {
                \Log::error("Ошибка обновления данных OLX: " . $e->getMessage());
            }
        }
    }
}
