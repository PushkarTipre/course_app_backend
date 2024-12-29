<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function login(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'avatar' => 'required',
                    'type' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                    'open_id' => 'required',
                    //'password' => 'required|min:6'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            //contains all user field val to save in DB
            $validated = $validateUser->validated();

            $map = [];
            $map['type'] = $validated['type'];
            $map['open_id'] = $validated['open_id'];
            $user = User::where($map)->first();

            //user logged in or not and save user in DB if not exists
            if (empty($user->id)) {
                $validated["token"] = md5(uniqid() . rand(10000, 99999));
                $validated['created_at'] = Carbon::now();
                $validated['unique_id'] = uniqid(); // Add unique_id to the user's data
                //$validated['password']= Hash::make($validated['password']);
                $userID = USer::insertGetId($validated);
                $userInfo = User::where('id', '=', $userID)->first();
                $accessToken = $userInfo->createToken(uniqid())->plainTextToken;
                $userInfo->access_token = $accessToken;
                User::where('id', '=', $userID)->update(['access_token' => $accessToken]);


                return response()->json([
                    'code' => 200,
                    'msg' => 'User Created Successfully',
                    'data' => $userInfo
                ], 200);
            }
            //user previously logged in
            $accessToken = $user->createToken(uniqid())->plainTextToken;
            $user->access_token = $accessToken;
            User::where('open_id', '=', $validated['open_id'])->update(['access_token' => $accessToken]);
            return response()->json([
                'code' => 200,
                'msg' => 'User Logged In Successfully',
                'data' => $user
            ], 200);


            // $user = User::create([
            //     'name' => $request->name,
            //     'email' => $request->email,
            //     'password' => Hash::make($request->password)
            // ]);


        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            // Validate the request
            $validateUser = Validator::make($request->all(), [
                'username' => 'nullable|string|max:255',
                'avatar' => 'nullable|string',
                'job' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 422);
            }

            // Get the authenticated user
            $user = $request->user();

            // Update the user
            $user->update([
                'name' => $request->username,
                'avatar' => $request->avatar,
                'job' => $request->job,
                'description' => $request->description,
            ]);

            // Return the updated user information
            return response()->json([
                'code' => 200,
                'msg' => 'User Updated Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
