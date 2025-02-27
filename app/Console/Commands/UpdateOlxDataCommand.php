<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateOlxDataJob;

class UpdateOlxDataCommand extends Command
{
    protected $signature = 'olx:update-data';
    protected $description = 'Обновляет данные объявлений с OLX API';

    public function handle()
    {
        UpdateOlxDataJob::dispatch()->onQueue('olx');
        $this->info('Запущено обновление данных с OLX.');
    }
}
