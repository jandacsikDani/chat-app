<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FriendsTest extends TestCase{
    use RefreshDatabase;

    public function test_send_friend_request()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($userA, 'sanctum')
        ->postJson("/api/friend-requests", [
            'friend_id' => $userB->id
        ])
        ->assertStatus(201)
        ->assertJson(['message' => 'Friend request sent']);

        $this->assertDatabaseHas('friends', [
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => 'pending'
        ]);
    }

    public function test_accept_friend_request()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        Friend::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => 'pending'
        ]);

        $this->actingAs($userB, 'sanctum')
        ->patchJson("/api/friend-requests/{$userA->id}", [
            'status' => 'accept'
        ])
        ->assertStatus(200)
        ->assertJson(['message' => 'Friend request accepted']);

        $this->assertDatabaseHas('friends', [
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => 'accepted'
        ]);

        $this->assertDatabaseHas('friends', [
            'user_id' => $userB->id,
            'friend_id' => $userA->id,
            'status' => 'accepted'
        ]);
    }

    public function test_decline_friend_request(){
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        Friend::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => 'pending'
        ]);

        $this->actingAs($userB, 'sanctum')
        ->patchJson("/api/friend-requests/{$userA->id}", [
            'status' => 'decline'
        ])
        ->assertStatus(200)
        ->assertJson(['message' => 'Friend request declined']);
    }

    public function test_list_friends()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        Friend::create(['user_id'=>$userA->id,'friend_id'=>$userB->id,'status'=>'accepted']);
        Friend::create(['user_id'=>$userB->id,'friend_id'=>$userA->id,'status'=>'accepted']);

        $this->actingAs($userA, 'sanctum')
        ->getJson("/api/friends")
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $userB->id]);
    }

    public function test_list_friend_requests()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        Friend::create([
            'user_id'=>$userA->id,
            'friend_id'=>$userB->id,
            'status'=>'pending'
        ]);

        $this->actingAs($userA, 'sanctum')
        ->getJson("/api/friend-requests")
        ->assertStatus(200)
        ->assertJsonFragment(['user_id' => $userA->id]);
    }

    public function test_incoming_friend_requests(){
        $receiver = User::factory()->create();
        $sender = User::factory()->create();

        Friend::create([
            'user_id' => $sender->id,
            'friend_id' => $receiver->id,
            'status' => 'pending'
        ]);

        $this->actingAs($receiver, 'sanctum')
        ->getJson('/api/friend-requests/incoming')
        ->assertStatus(200)
        ->assertJsonFragment([
            'user_id' => $sender->id,
            'friend_id' => $receiver->id,
            'status' => 'pending'
        ]);
    }

    public function test_outgoing_friend_requests(){
        $receiver = User::factory()->create();
        $sender = User::factory()->create();

        Friend::create([
            'user_id' => $sender->id,
            'friend_id' => $receiver->id,
            'status' => 'pending'
        ]);

        $this->actingAs($sender, 'sanctum')
        ->getJson('/api/friend-requests/outgoing')
        ->assertStatus(200)
        ->assertJsonFragment([
            'user_id' => $sender->id,
            'friend_id' => $receiver->id,
            'status' => 'pending'
        ]);
    }

    public function test_outgoing_returns_message_when_no_requests()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
        ->getJson('/api/friend-requests/outgoing')
        ->assertStatus(200)
        ->assertJson([
            'message' => 'You have no outgoing requests.'
        ]);
    }
}