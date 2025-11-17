<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Friend;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FriendsTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_pending_returns_only_pending_requests()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $userC = User::factory()->create();

        // pending és accepted rekordok
        Friend::create(['user_id' => $userA->id, 'friend_id' => $userB->id, 'status' => 'pending']);
        Friend::create(['user_id' => $userA->id, 'friend_id' => $userC->id, 'status' => 'accepted']);

        $pending = Friend::pending()->get();

        $this->assertCount(1, $pending);
        $this->assertEquals('pending', $pending->first()->status);
        $this->assertEquals($userB->id, $pending->first()->friend_id);
    }

    public function test_scope_accepted_returns_only_accepted_requests()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Friend::create(['user_id' => $userA->id, 'friend_id' => $userB->id, 'status' => 'accepted']);

        $accepted = Friend::accepted()->get();

        $this->assertCount(1, $accepted);
        $this->assertEquals('accepted', $accepted->first()->status);
    }

    public function test_user_relationship_returns_correct_user()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $friend = Friend::create(['user_id' => $userA->id, 'friend_id' => $userB->id, 'status' => 'pending']);

        $this->assertEquals($userA->id, $friend->user->id);
    }

    public function test_friend_relationship_returns_correct_friend()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $friend = Friend::create(['user_id' => $userA->id, 'friend_id' => $userB->id, 'status' => 'pending']);

        $this->assertEquals($userB->id, $friend->friend->id);
    }
}