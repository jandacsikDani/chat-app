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
            ->postJson("/api/friends/add/{$userB->id}")
            ->assertStatus(200)
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
            ->postJson("/api/friends/accept/{$userA->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Friend request accepted']);

        // Ellenőrizzük mindkét irányt
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

    public function test_list_friends()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        // Kétirányú kapcsolat
        Friend::create(['user_id'=>$userA->id,'friend_id'=>$userB->id,'status'=>'accepted']);
        Friend::create(['user_id'=>$userB->id,'friend_id'=>$userA->id,'status'=>'accepted']);

        $this->actingAs($userA, 'sanctum')
            ->getJson("/api/friends")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $userB->id]);
    }

    public function test_friend_requests_list()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['email_verified_at' => now()]);

        Friend::create(['user_id'=>$userA->id,'friend_id'=>$userB->id,'status'=>'pending']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/friends/requests")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $userB->id]);
    }
}