<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        event(new Registered($user));

        return response()->json([
            'message' => 'The registration was successfull',
            'user' => $user
        ], 201);
    }

    public function login(Request $request){
        $request ->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $user = User::where('email', $request->email)->first();

        if(!Auth::attempt($request->only('email', 'password'))){
            return response()->json([
                'message' => 'Wrong email or password.'
            ], 401);
        }


        if($user->email_verified_at === null){
            return response()->json([
                'message' => 'The email address is not verified.'
            ], 403);
        }

        $token = $user->createToken($user->name.'Auth-Token')->plainTextToken;

        return response()->json([
            'message' => 'Login succesfull',
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
