<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;

class MessageController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'receiver_id' => ['required', 'number'],
            'message' => ['required', 'string', 'max:5000']
        ]);
        $receiverId = $request->receiver_id;

        $sender = $request->user();
        $receiver = User::findOrFail($receiverId);

        if(!$sender->friends()->where('friend_id', $receiverId)->exists()){
            return response()->json([
                'error' => 'You can only send messages to friends'
            ], 403);                        
        }

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'message' => $request->message
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }

    public function index(Request $request){

        $messages = Message::with(['sender', 'receiver'])
        ->where('sender_id', $request->user()->id)
        ->orWhere('receiver_id', $request->user()->id)
        ->orderBy('created_at', 'asc')
        ->get();

        if($messages->isEmpty()){
            return response()->json([
                'message' => 'You have no messages.'
            ],404);
        }

        return response()->json($messages);
    }

    public function show(Request $request, $userId){
        $authId = $request->user()->id;
        $receiverId = User::findOrFail($userId);

        $messages = Message::with(['sender', 'receiver'])
        ->where(function($query) use ($authId, $receiverId){
            $query->where('sender_id', $authId)
            ->where('receiver_id', $receiverId);
        })
        ->orWhere(function($query) use ($authId, $receiverId){
            $query->where('sender_id', $receiverId)
            ->where('receiver_id', $authId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        if($messages->isEmpty()){
            return response()->json([
                'message' => 'You have no messages with this user.'
            ],404);
        }

        return response()->json($messages);
    }
}
