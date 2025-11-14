<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notify extends Component
{
    public $notifications;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = Notification::where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', Auth::id());
            })->latest()->get();
    }

    public function markAllRead()
    {
        Notification::where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', Auth::id());
            })->where('is_read', false)
            ->update(['is_read' => true]);

        $this->loadNotifications();
    }

    public function markRead($id)
    {
        $notif = Notification::find($id);
        if ($notif && ! $notif->is_read) {
            $notif->update(['is_read' => true]);
            $this->loadNotifications();
        }
    }

    public function render()
    {
        $total  = $this->notifications->count();
        $unread = $this->notifications->where('is_read', false)->count();
        $read   = $total - $unread;

        $stats = [
            ['label' => 'Total',  'count' => $total,  'color' => 'gray'],
            ['label' => 'Unread', 'count' => $unread, 'color' => 'red'],
            ['label' => 'Read',   'count' => $read,   'color' => 'green'],
        ];

        return view('livewire.notify', compact('stats'));
    }
}
