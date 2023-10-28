<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'メールアドレスかパスワードが間違っています。'], 401);
        }



        $credentials = [
            'user_id' => auth()->user()->user_id,
            'role_id' => auth()->user()->role_id,
            'name' => auth()->user()->name
        ];

        if(auth()->user()->status == '2' || auth()->user()->status == '3') {
            return response()->json(['message' => 'あなたのアカウントはブロックされています'], 401);
        }
        $usertoken = JWT::encode($credentials, env("JWT_SECRET"), 'HS256');
        return $this->createNewToken($token, $usertoken);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
             return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'ユーザーが正常に登録されました',
            'user' => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'ユーザーは正常にサインアウトしました']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {

        $credentials = [
            'user_id' => auth()->user()->user_id,
            'role_id' => auth()->user()->role_id,
            'name' => auth()->user()->name
        ];
        $usertoken = JWT::encode($credentials, env("JWT_SECRET"), 'HS256');

        return $this->createNewToken(auth()->refresh(), $usertoken);
    }

    /** 
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        // dd(auth()->user()->role_id);    
        return response()->json(['user' => auth()->user()]);
        // return response()->json(['message' => "sss"], 403);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token, $usertoken){
        return response()->json([
            'access_token' => $token,
            'user_token' => $usertoken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

}
