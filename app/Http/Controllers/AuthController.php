<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoginSessions;
use App\Models\UserProfile;
use Validator;
use App\Mail\WelcomeMail;
use Mail;

use App\Helper;

/**
*
*
* @author Daniel Ozeh hello@danielozeh.com.ng
*/

class AuthController extends Controller
{

    public function __constuct() {
        //$this->middleware('auth:api', ['except' => ['login', 'register', 'verifyUser']]);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6'
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        $ip_address = Helper::getRealIpAddr();
        $verification_code = Helper::generateCode(8);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'ip_address' => $ip_address,
            'password' => bcrypt($request->password),
            'verification_code' => $verification_code,
            'role_id' => 3
        ]);

        $user_id = $user->id;

        $user_profile = UserProfile::create([
            'user_id' => $user_id
        ]);

        //$myEmail = 'hello@danielozeh.com.ng';

        $details = [
            'title' => 'Verification Mail from Simple Blog',
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'verification_code' => $verification_code
            //'body' => 'Hello '. $request->full_name . '  Welcome to Simple Blog. Your Verification Code is ' . $verification_code . '.'
        ];

        Mail::to($request->email)->send(new WelcomeMail($details));

        return response()->json([
            'status' => 'success',
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    public function resendVerificationCode(Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|max:100',
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        $verification_code = Helper::generateCode(8);

        $user = User::where('email', $request->email)->first();
        if($user) {
            $user->verification_code = $verification_code;
            $user->save();

            $details = [
                'title' => 'Verification Mail from Simple Blog',
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'verification_code' => $verification_code
                //'body' => 'Hello '. $request->full_name . '  Welcome to Simple Blog. Your Verification Code is ' . $verification_code . '.'
            ];

            Mail::to($request->email)->send(new WelcomeMail($details));

            return response()->json([
                'status' => 'success',
                'message' => 'Verification Code Resent',
            ], 200);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid Email Address'
        ]);
        
    }

    public function verifyUser(Request $request, $email) {
        if(User::where('email', $email)->exists()) {

            $validator = Validator::make($request->all(),[
                'verification_code' => 'required',
            ]);

            if($validator->fails()) {
                return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
            }

            $verify = User::where('verification_code', $request->verification_code)->get();

            //return response()->json(['status' => 'failed', 'message' => $verify], 404);
            if($verify) {
                $id = $verify[0]->id;
                $user = User::find($id);
                $user->verification_code = $request->verification_code;
                $user->is_verified = 1;

                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User Verified successfully!'
                ], 200);
            }
            else {
                return response()->json(['status' => 'failed', 'message' => 'Invalid Verification Code'], 401);
            }
            
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'User Does not exist'], 404);
        }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if(!$token = auth()->attempt($validator->validated())) {
            return response()->json(['status' => 'failed', 'message' => 'Invalid Credentials!'], 401);
        }

        if(auth()->user()->is_verified == 0) {
            return response()->json(['status' => 'failed', 'message' => 'Account is not verified!'], 401);
        }

        if(auth()->user()->is_active == 0) {
            return response()->json(['status' => 'failed', 'message' => 'Account is Blocked!'], 401);
        }

        return $this->createNewToken($token);
    }

    protected function createNewToken($token){
        $ip_address = Helper::getRealIpAddr();

        $login = LoginSessions::create([
            'user_id' => auth()->user()->id,
            'ip_address' => $ip_address
        ]);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 86400,
            'user' => auth()->user()
        ]);
    }

    public function logout() {
        auth()->logout();
        return response()->json(['status' => 'success', 'message' => 'User successfully signed out']);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile() {
        $user = User::with('user_profile')->find(auth()->user()->id);

        if($user) {
            $user_id = auth()->user()->id;
            $user_profile = UserProfile::find($user_id);

            $user = [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "email" => $user->email,
                "role_id" => $user->role_id,
                "is_active" => $user->is_active,
                "is_verified" => $user->is_verified,
                "avatar" => $user_profile->avatar,
                "image_path" => Helper::imagePath(). '/users'
            ];

            return response()->json(['status' => 'success', 'user' => $user, 'user_profile' => $user_profile, 'image_path' => Helper::imagePath(). '/users'], 200);
        }
        
    }
}
