<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;

class MessageController extends Controller
{
    public function send(Request $request, $receiverId){
        $request->validate([
            'message' => ['required', 'string', 'max:5000']
        ]);

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
}
