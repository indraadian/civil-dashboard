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

        $notifications = $user->notifications()
            ->latest()
            ->take(20)
            ->get()
            ->unique(function ($notification) {
                // Filter out duplicate notification records generated within the same minute
                return ($notification->data['message'] ?? $notification->id) . '_' . $notification->created_at->format('Y-m-d H:i');
            })
            ->values()
            ->take(10)
            ->map(function ($notification) {
                return [
                    'id'           => $notification->id,
                    'type'         => $notification->data['type'] ?? 'info',
                    'message'      => $notification->data['message'] ?? '',
                    'download_url' => $notification->data['download_url'] ?? null,
                    'is_read'      => $notification->read_at !== null,
                    'created_at'   => $notification->created_at->diffForHumans(),
                ];
            });

        $unreadCount = $user->unreadNotifications()
            ->latest()
            ->get()
            ->unique(function ($notification) {
                return ($notification->data['message'] ?? $notification->id) . '_' . $notification->created_at->format('Y-m-d H:i');
            })
            ->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
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
