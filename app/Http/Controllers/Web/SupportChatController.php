<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Status;
use App\Models\SupportConversation;
use App\Models\User;
use App\Models\UserType;
use App\Services\SupportChat\Web\SupportChatInbox;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportChatController extends Controller
{
    public function __construct(private readonly SupportChatInbox $inbox) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $this->staff($request);
        $selected = null;

        if ($request->filled('c')) {
            $conversation = $this->inbox->conversation($request->integer('c'));

            if (! $conversation) {
                return redirect()->route('support-chat.index'); // bad/foreign id
            }

            // Mark read BEFORE building the list so the unread badges are correct
            $this->inbox->markRead($conversation, $user);

            $selected = [
                'id' => $conversation->id,
                'status' => $conversation->conversable->status,
                'messages' => $conversation->messages,
            ];
        }

        return Inertia::render('support-chat/Index', [
            'tickets' => $this->inbox->tickets($user),
            'selectedConversation' => $selected,
        ]);
    }

    public function markRead(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $this->staff($request);

        abort_unless($conversation->conversable_type === SupportConversation::class, 404);

        $this->inbox->markRead($conversation, $user);

        return back();
    }

    private function staff(Request $request): User
    {
        $user = $request->user();

        abort_unless(
            $user->user_type_id === UserType::SUPER_ADMIN && $user->status_id === Status::ACTIVE,
            403
        );

        return $user;
    }
}