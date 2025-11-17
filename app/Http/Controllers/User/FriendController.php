<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function addFriend(Request $request, $friendId){
        $user = $request->user();

        if($user->id === $friendId){
            return response()->json([
                'message' => 'You cannot add yourself as a friend'
            ],400);
        }

        $friend = User::whereNotNull('email_verified_at')->find($friendId);

        if(!$friend){
            return response()->json([
                'message' => 'User is not active or does not exists'
            ],404);
        }

        $existing = $user->friends()->where('friend_id', $friendId)->first();

        if($existing){
            return response()->json([
                'message' => 'Already pendig or friends'
            ], 409);
        }

        $user->friends()->attach($friendId, ['status' => 'pending']);

        return response()->json([
            'message' => 'Friend request sent'
        ]);
    }

    public function acceptFriend(Request $request, $friendId){
        $user = $request->user();

        $pending = Friend::where('user_id', $friendId)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if(!$pending){
            return response()->json([
                'message' => 'No pending request'
            ], 404);
        }

        $pending->status = 'accepted';
        $pending->save();

        Friend::firstOrCreate([
            'user_id' => $user->id,
            'friend_id' => $friendId
        ],[
            'status' => 'accepted'
        ]);
        
        return response()->json([
            'message' => 'Friend request accepted'
        ]);
    }

    public function listFriends(Request $request){
        $friends = $request->user()->friends()->get(['users.id', 'users.name', 'users.email']);
        return response()->json($friends);
    }

    public function friendRequests(Request $request){
        $user = $request->user();

    $requests = Friend::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->with(['friend'])
                    ->get();

    return response()->json($requests);
    }   
}
