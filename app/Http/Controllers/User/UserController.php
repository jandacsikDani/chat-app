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
        
        $perPage = $request->input('per_page', 5);

        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    public function show(Request $request, $userId){
        $query = User::query()-> whereNotNull('email_verified_at')->where('id', '!=', $request->user()->id)
                    ->where('id', $userId);

        if($query->count() === 0){
            return response()->json([
                'message' => 'No user found with the specified id'
            ], 404);
        }

        return response()->json($query->first());
    }
}
