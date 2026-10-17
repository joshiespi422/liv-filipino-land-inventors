<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('notifications/Index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20)
                ->through(fn (DatabaseNotification $n) => [
                    'id' => $n->id,
                    'message' => $n->data['body'] ?? $n->data['title'] ?? '',
                    'preview' => $n->data['latest_message'] ?? null,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ]),
        ]);
    }

    public function markRead(Request $request, string $notificationId): RedirectResponse
    {
        $notification = $request->user()->notifications()->find($notificationId);
        $notification?->markAsRead();

        $href = $notification ? $this->href($notification) : null;

        return $href ? redirect()->to($href) : back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
    
    /** Where a notification leads. Add one arm per new module. */
    private function href(DatabaseNotification $n): ?string
    {
        $conversationId = $n->data['conversation_id'] ?? null;

        if (! $conversationId) {
            return null;
        }

        return match ($n->data['conversation_type'] ?? null) {
            'support' => route('support-chat.index', ['c' => $conversationId], false),
            // 'intellectual' => route('...', ...), new module here
            default => null,
        };
    }
}
