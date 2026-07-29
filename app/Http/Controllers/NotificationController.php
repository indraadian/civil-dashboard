<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get recent notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count'  => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->take(10)->get()->map(function ($notification) {
                return [
                    'id'           => $notification->id,
                    'type'         => $notification->data['type'] ?? 'info',
                    'message'      => $notification->data['message'] ?? '',
                    'download_url' => $notification->data['download_url'] ?? null,
                    'is_read'      => $notification->read_at !== null,
                    'created_at'   => $notification->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Semua notifikasi telah ditandai dibaca']);
    }
}
