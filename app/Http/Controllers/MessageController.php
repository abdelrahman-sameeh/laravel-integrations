<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly ConversationService $conversationService) {}

    public function store(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $this->conversationService->sendDirectMessage(
            $request->user(),
            $user,
            $validated['content']
        );

        return redirect()->route('conversation.show', $user);
    }
}
