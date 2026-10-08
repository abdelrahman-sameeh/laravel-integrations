<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(private readonly ConversationService $conversationService) {}

    public function show(Request $request, User $user): View
    {
        $conversation = $this->conversationService->findDirectConversation(
            $request->user(),
            $user
        );

        $messages = collect();

        if ($conversation) {
            $messages = $conversation->messages()
                ->with('sender:id,name')
                ->latest()
                ->paginate(50);

            $messages->setCollection(
                $messages->getCollection()->reverse()->values()
            );
        }

        return view('conversation', [
            'conversation' => $conversation,
            'friend' => $user,
            'messages' => $messages,
            'sendMessageUrl' => route('message.store', $user),
        ]);
    }
}
