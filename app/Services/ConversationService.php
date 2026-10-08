<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    public function findDirectConversation(User $user, User $friend): ?Conversation
    {
        $this->ensureAcceptedFriendship($user, $friend);

        return $this->directConversationQuery($user, $friend)->first();
    }

    public function sendDirectMessage(User $sender, User $friend, string $content): Message
    {
        return DB::transaction(function () use ($sender, $friend, $content) {
            $this->ensureAcceptedFriendship($sender, $friend, lockForUpdate: true);

            $conversation = $this->directConversationQuery($sender, $friend)->first();

            if (! $conversation) {
                $conversation = Conversation::query()->create([
                    'is_group' => false,
                    'name' => null,
                ]);

                $conversation->users()->attach([$sender->id, $friend->id]);
            }

            return $conversation->messages()->create([
                'sender_id' => $sender->id,
                'content' => $content,
            ]);
        });
    }

    private function ensureAcceptedFriendship(User $user, User $friend, bool $lockForUpdate = false): Friendship
    {
        if ($user->is($friend)) {
            throw new AuthorizationException('لا يمكنك بدء محادثة مع نفسك.');
        }

        [$userId, $friendId] = $user->id < $friend->id
            ? [$user->id, $friend->id]
            : [$friend->id, $user->id];

        $query = Friendship::query()
            ->where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->where('status', Friendship::STATUS_ACCEPTED);

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $friendship = $query->first();

        if (! $friendship) {
            throw new AuthorizationException('المحادثات متاحة فقط بين الأصدقاء.');
        }

        return $friendship;
    }

    private function directConversationQuery(User $user, User $friend): Builder
    {
        return Conversation::query()
            ->where('is_group', false)
            ->whereHas('users', fn (Builder $query) => $query->whereKey($user->id))
            ->whereHas('users', fn (Builder $query) => $query->whereKey($friend->id))
            ->has('users', '=', 2);
    }
}
