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
            $validateUser = Validator::make($request->all(), 
            [
                'avatar'=>'required',
                'type'=>'required',

            
                'name' => 'required',
                'email' => 'required',
                'open_id'=>'required',
                //'password' => 'required|min:6'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            //contains all user field val to save in DB
            $validated = $validateUser->validated();

            $map=[];
            $map['type']=$validated['type'];
            $map['open_id']=$validated['open_id'];
            $user = User::where($map)->first();

            //user logged in or not and save user in DB if not exists
            if(empty($user->id)){
               $validated["token"]=md5(uniqid().rand(10000,99999));
               $validated['created_at']= Carbon::now();
               //$validated['password']= Hash::make($validated['password']);
               $userID= USer::insertGetId($validated);
               $userInfo = User::where('id', '=',$userID)->first();
               $accessToken = $userInfo->createToken(uniqid())->plainTextToken;
               $userInfo->access_token = $accessToken;
               User::where('id', '=',$userID)->update(['access_token'=>$accessToken]);

               
            return response()->json([
                'code' => 200,
                'msg' => 'User Created Successfully',
                'data' => $userInfo
            ], 200);
            }
            //user previously logged in
            $accessToken = $user->createToken(uniqid())->plainTextToken;
            $user->access_token = $accessToken;
            User::where('open_id', '=',$validated['open_id'])->update(['access_token'=>$accessToken]);
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

    // /**
    //  * Login The User
    //  * @param Request $request
    //  * @return User
    //  */
    // public function loginUser(Request $request)
    // {
    //     try {
    //         $validateUser = Validator::make($request->all(), 
    //         [
    //             'email' => 'required|email',
    //             'password' => 'required'
    //         ]);

    //         if($validateUser->fails()){
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'validation error',
    //                 'errors' => $validateUser->errors()
    //             ], 401);
    //         }

    //         if(!Auth::attempt($request->only(['email', 'password']))){
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Email & Password does not match with our record.',
    //             ], 401);
    //         }

    //         $user = User::where('email', $request->email)->first();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'User Logged In Successfully',
    //             'token' => $user->createToken("API TOKEN")->plainTextToken
    //         ], 200);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $th->getMessage()
    //         ], 500);
    //     }
    // }
}