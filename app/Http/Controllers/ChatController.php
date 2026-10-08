<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $conversations = $user->conversations()
            ->where('is_group', false)
            ->whereHas('messages')
            ->with([
                'users:id,name,email',
                'latestMessage.sender:id,name',
            ])
            ->withMax('messages', 'created_at')
            ->orderByDesc('messages_max_created_at')
            ->paginate(15);

        $conversations->setCollection(
            $conversations->getCollection()
                ->map(function (Conversation $conversation) use ($user): array {
                    return [
                        'conversation' => $conversation,
                        'friend' => $conversation->users->firstWhere('id', '!=', $user->id),
                        'latestMessage' => $conversation->latestMessage,
                    ];
                })
                ->filter(fn (array $item) => $item['friend'] !== null)
                ->values()
        );

        return view('chat', compact('conversations'));
    }
}
