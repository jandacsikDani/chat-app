<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Friend;

class FriendRequestController extends Controller
{
    public function store(Request $request){
        $user = $request->user();

        $request->validate([
            'friend_id' => ['required', 'integer']
        ]);

        $friend = $request->friend_id;

        if($user->id === $friend){
            return response()->json([
                'message' => 'You cannot add yourself as a friend'
            ],400);
        }

        $friend = User::whereNotNull('email_verified_at')->find($friend);

        if(!$friend){
            return response()->json([
                'message' => 'User is not active or does not exists'
            ],404);
        }

        $existing = $user->friends()->where('friend_id', $friend)->first();

        if($existing){
            return response()->json([
                'message' => 'Already pendig or friends'
            ], 409);
        }

        $user->friends()->attach($friend, ['status' => 'pending']);

        return response()->json([
            'message' => 'Friend request sent'
        ], 201);
    }

    public function update(Request $request, $friendId){
        $user = $request->user();

        $request->validate([
            "status" => ['required', 'string']
        ]);

        $pending = Friend::where('user_id', $friendId)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if(!$pending){
            return response()->json([
                'message' => 'No pending request'
            ], 404);
        }

        if($request->status === "accept"){
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
        }else if($request->status === "decline"){
            Friend::where('user_id', $friendId)
            ->where('friend_id', $user->id)
            ->delete();

            return response()->json([
                'message' => 'Friend request declined'
            ]);
        }
    }

    public function index(Request $request){
        $userId = $request->user()->id;

        $req = Friend::where('status', 'pending')
        ->where(function($q) use ($userId){
            $q->where('friend_id', $userId)
            ->orWhere('user_id', $userId);
        })
        ->with(['user', 'friend'])
        ->get();

        if($req->isEmpty()){
            return response()->json([
                'message' => 'You have no friends'
            ]);
        }

        return response()->json($req);
    }

    public function incoming(Request $request){
        $req = Friend::where('friend_id', $request->user()->id)
        ->where('status', 'pending')
        ->with('user')
        ->get();

        if($req->isEmpty()){
            return response()->json([
                'message' => 'You have no incoming requests.'
            ]);
        }

        return response()->json($req);
    }

    public function outgoing(Request $request){
        $req = Friend::where('user_id', $request->user()->id)
        ->where('status', 'pending')
        ->with('friend')
        ->get();

        if($req->isEmpty()){
            return response()->json([
                'message' => 'You have no outgoing requests.'
            ]);
        }

        return response()->json($req);
    }
}
