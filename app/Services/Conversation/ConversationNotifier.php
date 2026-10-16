<?php

namespace App\Services\Conversation;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Status;
use App\Models\SupportConversation;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\NewMessageNotification;
use App\Services\SupportChat\Web\SupportChatNotifier;

class ConversationNotifier
{
    public function __construct(private readonly SupportChatNotifier $supportChatNotifier) {}

    public function notifyOthers(Conversation $conversation, Message $message, int $senderId): void
    {
        if ($conversation->conversable_type === SupportConversation::class) {
            $this->notifySupport($conversation, $message, $senderId);
        } else {
            $this->notifyParticipants($conversation, $message, $senderId);
        }

        broadcast(new MessageSent($message))->toOthers();
    }

    private function notifySupport(Conversation $conversation, Message $message, int $senderId): void
    {
        $ticket = $conversation->conversable;

        if (! $ticket) {
            return;
        }

        // Ticket owner (mobile) wrote in -> one aggregated notification per super admin (website)
        if ($senderId === $ticket->user_id) {
            User::query()
                ->where('user_type_id', UserType::SUPER_ADMIN)
                ->where('status_id', Status::ACTIVE)
                ->where('id', '!=', $senderId)
                ->each(fn (User $admin) => $this->supportChatNotifier->notifyStaff($admin, $conversation, $message));

            return;
        }

        // Staff replied -> ticket owner gets the same notification as before (mobile unchanged)
        $ticket->user?->notify(new NewMessageNotification($message));
    }

    /** Original behavior, untouched. */
    private function notifyParticipants(Conversation $conversation, Message $message, int $senderId): void
    {
        $participants = $conversation->participants()
            ->where('user_id', '!=', $senderId)
            ->with('user')
            ->get()
            ->pluck('user');

        $service = $conversation->conversable->service ?? null;
        $admins = $service ? $service->admins()->get() : collect();

        $participants->merge($admins)
            ->unique('id')
            ->reject(fn ($user) => $user->id === $senderId)
            ->each(fn ($user) => $user->notify(new NewMessageNotification($message)));
    }
}
