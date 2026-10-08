<?php

namespace Tests\Feature\Web;

use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepted_friends_can_open_a_draft_without_creating_a_conversation(): void
    {
        [$user, $friend] = $this->acceptedFriends();

        $this->actingAs($user)
            ->get(route('conversation.show', $friend))
            ->assertOk()
            ->assertSee($friend->name)
            ->assertSee('ابدأ المحادثة');

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_non_friends_cannot_open_or_send_to_a_conversation(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('conversation.show', $otherUser))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('message.store', $otherUser), ['content' => 'Hello'])
            ->assertForbidden();

        $this->assertDatabaseCount('conversations', 0);
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_pending_friendship_cannot_start_a_conversation(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $this->friendship($user, $friend, Friendship::STATUS_PENDING);

        $this->actingAs($user)
            ->get(route('conversation.show', $friend))
            ->assertForbidden();
    }

    public function test_first_message_creates_the_direct_conversation_and_its_members(): void
    {
        [$user, $friend] = $this->acceptedFriends();

        $this->actingAs($user)
            ->post(route('message.store', $friend), [
                'content' => 'First message',
                'sender_id' => $friend->id,
            ])
            ->assertRedirect(route('conversation.show', $friend));

        $conversation = Conversation::query()->sole();

        $this->assertFalse($conversation->is_group);
        $this->assertEqualsCanonicalizing(
            [$user->id, $friend->id],
            $conversation->users()->pluck('users.id')->all()
        );
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => 'First message',
        ]);
    }

    public function test_later_messages_reuse_the_same_direct_conversation(): void
    {
        [$user, $friend] = $this->acceptedFriends();

        $this->actingAs($user)
            ->post(route('message.store', $friend), ['content' => 'First'])
            ->assertRedirect();

        $this->actingAs($friend)
            ->post(route('message.store', $user), ['content' => 'Second'])
            ->assertRedirect();

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('conversation_user', 2);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_existing_direct_conversation_displays_its_messages(): void
    {
        [$user, $friend] = $this->acceptedFriends();
        $conversation = Conversation::query()->create([
            'is_group' => false,
        ]);
        $conversation->users()->attach([$user->id, $friend->id]);
        $conversation->messages()->create([
            'sender_id' => $friend->id,
            'content' => 'Existing message',
        ]);

        $this->actingAs($user)
            ->get(route('conversation.show', $friend))
            ->assertOk()
            ->assertSee('Existing message');

        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_group_conversation_with_both_users_is_not_used_as_their_direct_chat(): void
    {
        [$user, $friend] = $this->acceptedFriends();
        $thirdUser = User::factory()->create();
        $group = Conversation::query()->create([
            'is_group' => true,
            'name' => 'Group',
        ]);
        $group->users()->attach([$user->id, $friend->id, $thirdUser->id]);

        $this->actingAs($user)
            ->post(route('message.store', $friend), ['content' => 'Direct message'])
            ->assertRedirect();

        $this->assertDatabaseCount('conversations', 2);
        $this->assertDatabaseHas('messages', [
            'content' => 'Direct message',
            'sender_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $group->id,
            'content' => 'Direct message',
        ]);
    }

    public function test_message_content_is_required_and_limited(): void
    {
        [$user, $friend] = $this->acceptedFriends();

        $this->actingAs($user)
            ->from(route('conversation.show', $friend))
            ->post(route('message.store', $friend), ['content' => ''])
            ->assertRedirect(route('conversation.show', $friend))
            ->assertSessionHasErrors('content');

        $this->actingAs($user)
            ->from(route('conversation.show', $friend))
            ->post(route('message.store', $friend), [
                'content' => str_repeat('a', 2001),
            ])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('conversations', 0);
    }

    private function acceptedFriends(): array
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $this->friendship($user, $friend, Friendship::STATUS_ACCEPTED);

        return [$user, $friend];
    }

    private function friendship(User $user, User $friend, string $status): Friendship
    {
        return Friendship::query()->create([
            'user_id' => min($user->id, $friend->id),
            'friend_id' => max($user->id, $friend->id),
            'requested_by' => $user->id,
            'status' => $status,
        ]);
    }
}
