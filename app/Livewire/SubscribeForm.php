<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;
use App\Models\Subscription;
use App\Jobs\ProcessOlxIdJob;

class SubscribeForm extends Component
{
    public $url;

    public function subscribe()
    {
        $this->validate(['url' => 'required|url']);

        $user = Auth::user();

        $announcement = Announcement::firstOrCreate(
            ['url' => $this->url],
            ['title' => 'Pending...']
        );

        Subscription::firstOrCreate([
            'user_id' => $user->id,
            'announcement_id' => $announcement->id,
        ]);

        // Запускаем фоновую обработку OLX ID
        ProcessOlxIdJob::dispatch($announcement)->onQueue('olx');

        // Отправляем событие, чтобы обновить список подписок
        $this->dispatch('subscription-added');

        // Уведомляем пользователя
        $this->dispatch('subscription:success', 'Ссылка добавлена! OLX ID будет найден позже.');

        // Очищаем поле ввода
        $this->url = '';
    }

    public function render()
    {
        return view('livewire.subscribe-form');
    }
}
