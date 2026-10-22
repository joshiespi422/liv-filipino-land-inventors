<?php

namespace App\Services\SupportChat\Web;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\SupportConversation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupportChatInbox
{
    public function __construct(private readonly SupportChatNotifier $notifier) {}

    /** Open tickets first, then newest activity. */
    public function tickets(User $user, string $status = SupportConversation::OPEN, int $limit = 100): Collection
    {
        $lastMessage = fn (string $column) => Message::query()
            ->select($column)
            ->whereColumn('messages.conversation_id', 'conversations.id')
            ->orderByDesc('messages.id')
            ->limit(1);

        $unread = Message::query()
            ->selectRaw('count(*)')
            ->whereColumn('messages.conversation_id', 'conversations.id')
            ->where('messages.sender_id', '!=', $user->id)
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('conversation_participants as cp')
                ->whereColumn('cp.conversation_id', 'messages.conversation_id')
                ->where('cp.user_id', $user->id)
                ->whereColumn('cp.last_read_at', '>=', 'messages.created_at'));

        return SupportConversation::query()
            ->select('support_conversations.*')
            ->join('conversations', function ($join) {
                $join->on('conversations.conversable_id', '=', 'support_conversations.id')
                    ->where('conversations.conversable_type', '=', SupportConversation::class);
            })
            ->where('support_conversations.status', $status)
            ->addSelect('conversations.id as conversation_id')
            ->addSelect([
                'last_message_at' => $lastMessage('created_at'),
                'last_message_body' => $lastMessage('body'),
                'unread_count' => $unread,
            ])
            ->with('user:id,name,email')
            ->orderByDesc('last_message_at')
            ->orderByDesc('support_conversations.id')
            ->limit($limit)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'conversation_id' => (int) $t->conversation_id,
                'status' => $t->status,
                'subject' => $t->subject,
                'user' => $t->user,
                'last_message' => $t->last_message_body ?? ($t->last_message_at ? 'Sent an attachment' : null),
                'last_message_at' => $t->last_message_at ? Carbon::parse($t->last_message_at)->toIso8601String() : null,
                'unread_count' => (int) $t->unread_count,
            ]);
    }

    /** Support conversations only. Other conversation types return null. */
    public function conversation(int $id): ?Conversation
    {
        $conversation = Conversation::query()
            ->where('conversable_type', SupportConversation::class)
            ->with('conversable')
            ->find($id);

        $conversation?->setRelation(
            'messages',
            $conversation->messages()
                ->with(['sender:id,name', 'attachments'])
                ->latest('id')
                ->limit(100)
                ->get()
                ->reverse()
                ->values()
        );

        return $conversation;
    }

    public function markRead(Conversation $conversation, User $user): void
    {
        $conversation->ensureParticipant($user)->update(['last_read_at' => now()]);
        $this->notifier->clearFor($user, $conversation->id);
    }

    public function setStatus(Conversation $conversation, string $status): void
    {
        DB::transaction(function () use ($conversation, $status) {
            $conversation->conversable->update(['status' => $status]);
            $conversation->update(['status' => $status]);
        });
    }
}