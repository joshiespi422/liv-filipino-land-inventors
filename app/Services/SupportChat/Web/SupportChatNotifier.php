<?php

namespace App\Services\SupportChat\Web;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\Web\SupportChatMessageNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupportChatNotifier
{
    public function notifyStaff(User $recipient, Conversation $conversation, Message $message): void
    {
        $message->loadMissing('sender:id,name');

        [$id, $data] = DB::transaction(function () use ($recipient, $conversation, $message) {
            $existing = $this->unreadFor($recipient, $conversation->id, lock: true)->first();

            $count = ($existing?->data['unread_count'] ?? 0) + 1;
            $senderName = $message->sender->name ?? 'a user';

            $data = [
                'conversation_id' => $conversation->id,
                'conversation_type' => 'support',
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $senderName,
                'unread_count' => $count,
                'latest_message' => Str::limit($message->body ?: 'Sent an attachment', 80),
                'body' => $count === 1
                    ? "1 new message in chat support from {$senderName}"
                    : "{$count} new messages in chat support from {$senderName}",
            ];

            if ($existing) {
                $existing->forceFill(['data' => $data, 'created_at' => now()])->save();

                return [$existing->id, $data];
            }

            $id = (string) Str::uuid();
            $recipient->notifications()->create([
                'id' => $id,
                'type' => SupportChatMessageNotification::class,
                'data' => $data,
            ]);

            return [$id, $data];
        });

        $notification = new SupportChatMessageNotification($data);
        $notification->id = $id;

        $recipient->notify($notification); // broadcast only; DB row already written
    }

    /** Mark this conversation's unread support notification(s) as read. */
    public function clearFor(User $user, int $conversationId): void
    {
        $this->unreadFor($user, $conversationId)->each->markAsRead();
    }

    private function unreadFor(User $user, int $conversationId, bool $lock = false): Collection
    {
        $query = $user->unreadNotifications()
            ->where('type', SupportChatMessageNotification::class);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get()->filter(
            fn ($n) => ($n->data['conversation_id'] ?? null) === $conversationId
        );
    }
}