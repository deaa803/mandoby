<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'status' => true,
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications,
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Unread notifications count retrieved successfully',
            'data' => [
                'unread_count' => AppNotification::query()
                    ->where('user_id', $request->user()->id)
                    ->whereNull('read_at')
                    ->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, AppNotification $notification)
    {
        if ((int) $notification->user_id !== (int) $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'This notification does not belong to your account',
            ], 403);
        }

        $notification->update([
            'read_at' => $notification->read_at ?: now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read successfully',
            'data' => $notification->fresh(),
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'All notifications marked as read successfully',
        ]);
    }
}
