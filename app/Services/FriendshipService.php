<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FriendshipService
{
    public function send(User $sender, User $recipient): Friendship
    {
        if ($sender->is($recipient)) {
            throw ValidationException::withMessages([
                'friend' => 'لا يمكنك إرسال طلب صداقة لنفسك.',
            ]);
        }

        [$userId, $friendId] = $this->orderedPair($sender->id, $recipient->id);

        try {
            return DB::transaction(function () use ($sender, $userId, $friendId) {
                $friendship = Friendship::query()
                    ->where('user_id', $userId)
                    ->where('friend_id', $friendId)
                    ->lockForUpdate()
                    ->first();

                if (! $friendship) {
                    return Friendship::query()->create([
                        'user_id' => $userId,
                        'friend_id' => $friendId,
                        'requested_by' => $sender->id,
                        'status' => Friendship::STATUS_PENDING,
                    ]);
                }

                if ($friendship->status === Friendship::STATUS_ACCEPTED) {
                    throw ValidationException::withMessages([
                        'friend' => 'هذا المستخدم ضمن أصدقائك بالفعل.',
                    ]);
                }

                if ($friendship->status === Friendship::STATUS_PENDING) {
                    $message = $friendship->requested_by === $sender->id
                        ? 'تم إرسال طلب صداقة لهذا المستخدم من قبل.'
                        : 'لديك طلب صداقة معلّق من هذا المستخدم.';

                    throw ValidationException::withMessages(['friend' => $message]);
                }

                $friendship->update([
                    'requested_by' => $sender->id,
                    'status' => Friendship::STATUS_PENDING,
                ]);

                return $friendship->refresh();
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) !== '23000') {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'friend' => 'يوجد طلب صداقة بينكما بالفعل.',
            ]);
        }
    }

    public function accept(User $recipient, Friendship $friendship): Friendship
    {
        return $this->respond($recipient, $friendship, Friendship::STATUS_ACCEPTED);
    }

    public function decline(User $recipient, Friendship $friendship): Friendship
    {
        return $this->respond($recipient, $friendship, Friendship::STATUS_DECLINED);
    }

    private function respond(User $recipient, Friendship $friendship, string $status): Friendship
    {
        return DB::transaction(function () use ($recipient, $friendship, $status) {
            $lockedFriendship = Friendship::query()
                ->lockForUpdate()
                ->findOrFail($friendship->id);

            $this->ensureRecipientCanRespond($recipient, $lockedFriendship);
            $lockedFriendship->update(['status' => $status]);

            return $lockedFriendship->refresh();
        });
    }

    private function ensureRecipientCanRespond(User $recipient, Friendship $friendship): void
    {
        if (! $friendship->involves($recipient->id) || $friendship->requested_by === $recipient->id) {
            throw new AuthorizationException('غير مصرح لك بالتعامل مع هذا الطلب.');
        }

        if ($friendship->status !== Friendship::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'friend' => 'تمت معالجة طلب الصداقة من قبل.',
            ]);
        }
    }

    private function orderedPair(int $firstUserId, int $secondUserId): array
    {
        return $firstUserId < $secondUserId
            ? [$firstUserId, $secondUserId]
            : [$secondUserId, $firstUserId];
    }
}
