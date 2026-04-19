<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('target_type', 'user')
            ->where('target_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'message' => '',
        ]);
    }

    public function markRead($id)
    {
        $notification = Notification::where('target_type', 'user')
            ->where('target_id', auth()->id())
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Bildirim okundu olarak işaretlendi.',
        ]);
    }
}
