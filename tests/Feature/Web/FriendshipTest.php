<?php

namespace Tests\Feature\Web;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_friends_page(): void
    {
        $this->get('/friends')->assertRedirect('/login');
    }

    public function test_user_can_send_a_friend_request(): void
    {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $this->actingAs($sender)
            ->from('/friends')
            ->post(route('friends.store', $recipient))
            ->assertRedirect('/friends')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('friendships', [
            'user_id' => $recipient->id,
            'friend_id' => $sender->id,
            'requested_by' => $sender->id,
            'status' => Friendship::STATUS_PENDING,
        ]);
    }

    public function test_user_cannot_send_a_friend_request_to_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/friends')
            ->post(route('friends.store', $user))
            ->assertRedirect('/friends')
            ->assertSessionHasErrors('friend');

        $this->assertDatabaseCount('friendships', 0);
    }

    public function test_duplicate_and_reverse_pending_requests_are_prevented(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $this->actingAs($sender)
            ->post(route('friends.store', $recipient))
            ->assertSessionHasNoErrors();

        $this->actingAs($sender)
            ->post(route('friends.store', $recipient))
            ->assertSessionHasErrors('friend');

        $this->actingAs($recipient)
            ->post(route('friends.store', $sender))
            ->assertSessionHasErrors('friend');

        $this->assertDatabaseCount('friendships', 1);
    }

    public function test_recipient_can_accept_a_friend_request(): void
    {
        [$friendship, $sender, $recipient] = $this->pendingFriendship();

        $this->actingAs($recipient)
            ->from('/friends')
            ->patch(route('friend-requests.accept', $friendship))
            ->assertRedirect('/friends')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('friendships', [
            'id' => $friendship->id,
            'status' => Friendship::STATUS_ACCEPTED,
        ]);
    }

    public function test_sender_and_unrelated_user_cannot_accept_a_request(): void
    {
        [$friendship, $sender] = $this->pendingFriendship();
        $unrelatedUser = User::factory()->create();

        $this->actingAs($sender)
            ->patch(route('friend-requests.accept', $friendship))
            ->assertForbidden();

        $this->actingAs($unrelatedUser)
            ->patch(route('friend-requests.accept', $friendship))
            ->assertForbidden();

        $this->assertDatabaseHas('friendships', [
            'id' => $friendship->id,
            'status' => Friendship::STATUS_PENDING,
        ]);
    }

    public function test_recipient_can_decline_and_sender_can_request_again(): void
    {
        [$friendship, $sender, $recipient] = $this->pendingFriendship();

        $this->actingAs($recipient)
            ->delete(route('friend-requests.destroy', $friendship))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('friendships', [
            'id' => $friendship->id,
            'status' => Friendship::STATUS_DECLINED,
        ]);

        $this->actingAs($sender)
            ->post(route('friends.store', $recipient))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('friendships', [
            'id' => $friendship->id,
            'requested_by' => $sender->id,
            'status' => Friendship::STATUS_PENDING,
        ]);
        $this->assertDatabaseCount('friendships', 1);
    }

    public function test_friends_page_lists_incoming_requests_and_available_users(): void
    {
        [$friendship, $sender, $recipient] = $this->pendingFriendship();
        $availableUser = User::factory()->create();

        $this->actingAs($recipient)
            ->get('/friends')
            ->assertOk()
            ->assertSee($sender->name)
            ->assertSee($availableUser->name)
            ->assertSee(route('friend-requests.accept', $friendship), false);
    }

    private function pendingFriendship(): array
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $friendship = Friendship::query()->create([
            'user_id' => min($sender->id, $recipient->id),
            'friend_id' => max($sender->id, $recipient->id),
            'requested_by' => $sender->id,
            'status' => Friendship::STATUS_PENDING,
        ]);

        return [$friendship, $sender, $recipient];
    }
}
