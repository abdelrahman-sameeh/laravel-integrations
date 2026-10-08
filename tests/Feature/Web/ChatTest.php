<?php

namespace Tests\Feature\Web;

use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_direct_conversation_is_listed_for_both_participants(): void
    {
        [$firstUser, $secondUser, $conversation] = $this->directConversation();
        $conversation->messages()->create([
            'sender_id' => $firstUser->id,
            'content' => 'Shared message',
        ]);

        $this->actingAs($firstUser)
            ->get('/chat')
            ->assertOk()
            ->assertSee($secondUser->name)
            ->assertSee('Shared message');

        $this->actingAs($secondUser)
            ->get('/chat')
            ->assertOk()
            ->assertSee($firstUser->name)
            ->assertSee('Shared message');
    }

    public function test_user_does_not_see_conversations_they_do_not_belong_to(): void
    {
        [$firstUser, $secondUser, $conversation] = $this->directConversation();
        $outsider = User::factory()->create();
        $conversation->messages()->create([
            'sender_id' => $firstUser->id,
            'content' => 'Private conversation content',
        ]);

        $this->actingAs($outsider)
            ->get('/chat')
            ->assertOk()
            ->assertDontSee($firstUser->name)
            ->assertDontSee($secondUser->name)
            ->assertDontSee('Private conversation content');
    }

    public function test_draft_without_messages_is_not_listed_as_an_active_conversation(): void
    {
        [$firstUser, $secondUser] = $this->directConversation();

        $this->actingAs($firstUser)
            ->get('/chat')
            ->assertOk()
            ->assertDontSee($secondUser->email)
            ->assertSee('لا توجد محادثات بعد');
    }

    public function test_conversations_are_ordered_by_the_latest_message(): void
    {
        $user = User::factory()->create();
        $olderFriend = User::factory()->create();
        $newerFriend = User::factory()->create();
        $this->acceptedFriendship($user, $olderFriend);
        $this->acceptedFriendship($user, $newerFriend);

        $olderConversation = $this->conversationFor($user, $olderFriend);
        $newerConversation = $this->conversationFor($user, $newerFriend);

        $olderMessage = $olderConversation->messages()->create([
            'sender_id' => $olderFriend->id,
            'content' => 'Older message',
        ]);
        $olderMessage->forceFill([
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ])->save();
        $newerConversation->messages()->create([
            'sender_id' => $newerFriend->id,
            'content' => 'Newer message',
        ]);

        $this->actingAs($user)
            ->get('/chat')
            ->assertOk()
            ->assertSeeInOrder([$newerFriend->name, $olderFriend->name]);
    }

    private function directConversation(): array
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $this->acceptedFriendship($firstUser, $secondUser);

        return [
            $firstUser,
            $secondUser,
            $this->conversationFor($firstUser, $secondUser),
        ];
    }

    private function conversationFor(User $firstUser, User $secondUser): Conversation
    {
        $conversation = Conversation::query()->create(['is_group' => false]);
        $conversation->users()->attach([$firstUser->id, $secondUser->id]);

        return $conversation;
    }

    private function acceptedFriendship(User $firstUser, User $secondUser): Friendship
    {
        return Friendship::query()->create([
            'user_id' => min($firstUser->id, $secondUser->id),
            'friend_id' => max($firstUser->id, $secondUser->id),
            'requested_by' => $firstUser->id,
            'status' => Friendship::STATUS_ACCEPTED,
        ]);
    }
}
