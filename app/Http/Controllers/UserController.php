<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\UserProfile;
use App\Helper;
use Validator;
use Hash;

/**
 * @author Daniel Ozeh hello@danielozeh.com.ng
 */

class UserController extends Controller
{
    public function getAllUsers() {
        $users = User::with('userRole')->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'message' => $users
        ],200);
    }

    public function viewUserProfile(Request $request, $id) {
        if(auth()->user()->role_id == 1) {
            $user = User::find($id);

            if($user) {
                $user_profile = UserProfile::find($id);

                return response()->json(['status' => 'success', 'user' => $user, 'user_profile' => $user_profile, 'image_path' => Helper::imagePath(). '/users'], 200);
            }
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
        }
        
    }

    public function updateUserProfile(Request $request, $id) {
        if(auth('api')->user()->role_id == 1) {
            $user_profile = UserProfile::find($id);
            $user_profile->phone_number = $request->phone_number;
            $user_profile->gender = $request->gender;
            $user_profile->date_of_birth = $request->date_of_birth;
            $user_profile->address = $request->address;
            $user_profile->state = $request->state;
            $user_profile->country = $request->country;

            $user_profile->save();

            $user = User::find($id);
            $user->role_id = $request->role_id;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;

            $user->save();

            return response()->json(['status' => 'success', 'message' => 'User Profile Updated'], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
        }
        
    }

    public function updateProfile(Request $request) {
        $user_profile = UserProfile::find(auth()->user()->id);
        $user_profile->phone_number = $request->phone_number;
        $user_profile->gender = $request->gender;
        $user_profile->date_of_birth = $request->date_of_birth;
        $user_profile->address = $request->address;
        $user_profile->state = $request->state;
        $user_profile->country = $request->country;

        $user_profile->save();

        $user = User::find(auth()->user()->id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Profile Updated'], 200);
        
    }

    public function updateProfilePicture(Request $request) {
        $validator = Validator::make($request->all(),[
            'avatar' => 'required|mimes:png,jpg|max:2048'
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        if($avatar = $request->file('avatar')) {
            //$save_image = $request->avatar->store('public/users');
            //$size = $request->file('avatar')->getSize();

            //$avatar = $avatar->hashName();

            $size = $request->file('avatar')->getSize();
            $avatar = Helper::generateCode(12);

            $save_image = $request->avatar->move(public_path('/users'), $avatar);
        }
        $user_profile = UserProfile::find(auth('api')->user()->id);
        $user_profile->avatar = $avatar;

        $user_profile->save();

        return response()->json(['status' => 'success', 'message' => 'Profile Picture Updated'], 200);        
    }

    public function updateProfileCoverPhoto(Request $request) {
        $validator = Validator::make($request->all(),[
            'cover_photo' => 'required|mimes:png,jpg|max:2048'
        ]);

        if($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()], 400);
        }

        if($cover_photo = $request->file('cover_photo')) {
            $save_image = $request->cover_photo->store('public/users');
            $size = $request->file('cover_photo')->getSize();

            $cover_photo = $cover_photo->hashName();
        }
        $user_profile = UserProfile::find(auth('api')->user()->id);
        $user_profile->cover_photo = $cover_photo;

        $user_profile->save();

        return response()->json(['status' => 'success', 'message' => 'User Profile Cover Photo Updated'], 200);
        
    }

    public function generateCode($value) {
        $code = substr(md5(uniqid() . "" . time()), -$value);
        return $code;
    }

    public function forgotPassword(Request $request) {
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => 'required|email'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        //get user_id belonging to email
        $user_detail = User::where('email', $request->email)->first();

        if(!$user_detail) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email Address Does not Exist'
            ], 201);
        }

        //generate a new password and send to the user
        $new_password = $this->generateCode(9);

        $user = User::find($user_detail->id);
        $user->password = bcrypt($new_password);
        $user->save();

        $details = [
            'title' => 'New Password',
            'body' => 'Hello '. $request->email . ' Your New Password is ' . $new_password . '. You are advised to change your password after Login!'
        ];

        Mail::to($request->email)->send(new ForgotPasswordMail($details));

        return response()->json([
            'status' => 'success',
            'message' => 'Your New Password has been sent to your email'
        ], 200);
    }

    public function changePassword(Request $request) {
        $user_id = auth('api')->user()->id;

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string',
            'confirm_password' => 'required|string',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if($request->new_password != $request->confirm_password) {
            return response()->json([
                'status' => 'failed', 
                'message' => 'Passwords do not match'
            ]);
        }

        $user = User::find($user_id);

        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => 'success', 
                'message' => 'Password Updated Successfully!'
            ], 200);

        } else {
            return response()->json([
                'status' => 'failed', 
                'message' => 'Your Old Password Credential is Invalid!'
            ]);
        }
    }

    public function blockUser(Request $request, $id) {
        if(auth('api')->user()->role_id == 1) {
            $user = User::find($id); 
            $user->is_active = 0;
            
            $user->save();

            return response()->json([
                'status' => 'success', 
                'message' => 'User Blocked!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
        }
    }

    public function unblockUser(Request $request, $id) {
        if(auth('api')->user()->role_id == 1) {
            $user = User::find($id); 
            $user->is_active = 1;
            
            $user->save();

            return response()->json([
                'status' => 'success', 
                'message' => 'User Unblocked!'
            ], 200);
        }
        else {
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 401);
        }
    }
}
