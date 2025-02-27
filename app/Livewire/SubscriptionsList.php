<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Announcement;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class SubscriptionsList extends Component
{
    public $announcements;
    public $expandedAnnouncements = [];
    public $confirmingUnsubscribe = null;

    public function mount()
    {
        $this->loadAnnouncements();
    }

    #[On('subscription-added')]
    public function loadAnnouncements()
    {
        $this->announcements = Announcement::whereHas('subscriptions', function ($query) {
            $query->where('user_id', Auth::id());
        })->with(['prices' => function ($query) {
            $query->latest();
        }])->get();
    }

    public function togglePriceHistory($announcementId)
    {
        if (isset($this->expandedAnnouncements[$announcementId])) {
            unset($this->expandedAnnouncements[$announcementId]);
        } else {
            $this->expandedAnnouncements[$announcementId] = true;
        }
    }

    public function confirmUnsubscribe($announcementId)
    {
        $this->confirmingUnsubscribe = $announcementId;
    }

    public function unsubscribe()
    {
        if (!$this->confirmingUnsubscribe) {
            return;
        }

        Subscription::where('user_id', Auth::id())
            ->where('announcement_id', $this->confirmingUnsubscribe)
            ->delete();

        $this->confirmingUnsubscribe = null;
        $this->dispatch('subscription-removed', 'Подписка удалена.');
        $this->loadAnnouncements();
    }

    public function render()
    {
        return view('livewire.subscriptions-list');
    }
}
