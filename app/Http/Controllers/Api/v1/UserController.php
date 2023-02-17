<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\User;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Session;

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

class UserController extends Controller
{
    //

    public function register(request $request){

        // $validator = $request->validate([
        //     'email' => ['required', 'max:60'],
        //     'password' => ['required', 'max:60'],
        // ]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|max:60',
            'password' => 'required',
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'message' => $validator->errors(),
                'status' => 'failed',
            ]);
          
        }

        User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'status' => 'success',
        ]);
    }

    public function signin(request $request){


        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            $token = session()->token();
            $session_id = session()->getId();

            $ga = new GoogleAuthenticator;
            $secret = $ga->generateSecret();

            $qr_url = GoogleQrUrl::generate($token, $secret, $session_id);

            return response()->json([
                'message' => 'Please scan qr using your 2FA app from your mobile device.',
                'two_fa_qr_url' => $qr_url,
                'status' => 'success',
            ]);
        
        }

        return response()->json([
            'message' => 'Invalid user.',
            'status' => 'failed',
        ]);
        

    }
}
