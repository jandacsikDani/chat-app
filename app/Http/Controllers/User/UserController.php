<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request){
        $search = $request->query('search');

        $query = User::query()
            ->whereNotNull('email_verified_at')
            ->where('id', '!=', $request->user()->id);

        if($search){
            $query->where(function ($q) use ($search){
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if($query->count() === 0){
            return response()->json([
                'message' => 'No users found'
            ],404);
        }
        
        $users = $query->paginate(5);

        return response()->json($users);
    }
}
