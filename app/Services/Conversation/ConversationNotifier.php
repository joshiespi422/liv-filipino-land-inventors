<?php

namespace App\Services\Conversation;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\NewMessageNotification;
use App\Services\SupportChat\Web\SupportChatNotifier;

class ConversationNotifier
{
    public function __construct(private readonly SupportChatNotifier $supportChatNotifier) {}

    public function notifyOthers(Conversation $conversation, Message $message, int $senderId): void
    {
        $participants = $conversation->participants()
            ->where('user_id', '!=', $senderId)
            ->with('user')
            ->get()
            ->pluck('user');

        $service = $conversation->conversable->service ?? null;
        $admins = $service ? $service->admins()->get() : collect();

        $isSupport = $conversation->conversation_type === 'support';

        $participants->merge($admins)
            ->unique('id')
            ->reject(fn ($user) => $user->id === $senderId)
            ->each(function ($user) use ($conversation, $message, $isSupport) {
                // Website staff on support chat: one aggregated notification per conversation
                if ($isSupport && $this->isWebsiteStaff($user)) {
                    $this->supportChatNotifier->notifyStaff($user, $conversation, $message);

                    return;
                }

                // Everyone else (mobile members/basic, intellectual property chats): unchanged
                $user->notify(new NewMessageNotification($message));
            });

        broadcast(new MessageSent($message))->toOthers();
    }

    private function isWebsiteStaff(User $user): bool
    {
        return in_array($user->user_type_id, [UserType::SUPER_ADMIN], true);
    }
}
