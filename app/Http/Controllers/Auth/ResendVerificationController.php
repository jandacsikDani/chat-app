<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResendVerificationController extends Controller
{
    public function send(Request $request){
        if($request->user()->hasVerifiedEmail()){
            return response()->json([
                'message' => 'Email already verified.'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email resent.'
        ]);
    }
}
