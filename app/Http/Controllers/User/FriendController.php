<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use Illuminate\Http\Request;
use App\Models\User;

class FriendController extends Controller
{
    public function index(Request $request){
        $friends = $request->user()->friends()->get(['users.id', 'users.name', 'users.email']);
        return response()->json($friends);
    }
}
