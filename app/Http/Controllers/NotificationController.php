<?php

namespace App\Http\Controllers;

use App\Events\NotificationCreated;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Return the most recent notifications as JSON,
     * and include the unread count.
     */
    public function recent(Request $request): JsonResponse
    {
        $userId = Auth::id();

        // Fetch the 10 most recent global + user-specific
        $notifications = Notification::where(function ($q) use ($userId) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'type', 'title', 'message', 'level', 'is_read', 'created_at']);

        // Count how many of those are unread
        $unreadCount = Notification::where(function ($q) use ($userId) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $userId);
            })
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'level'      => $n->level,
                'is_read'    => $n->is_read,
                'timestamp'  => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * Mark all global + user-specific notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        $userId = Auth::id();

        Notification::where(function ($q) use ($userId) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $userId);
            })
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Return just the unread count for global + user-specific.
     */
    public function unreadCount(): JsonResponse
    {
        $userId = Auth::id();

        $count = Notification::where(function ($q) use ($userId) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $userId);
            })
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
