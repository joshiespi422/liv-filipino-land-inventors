<?php

namespace App\Notifications\Web;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Website-only (super admin / admin) summary notification for support chat.
 * The database row is upserted by SupportChatNotifier; this class only broadcasts it.
 */
class SupportChatMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $payload) {}

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload);
    }
}