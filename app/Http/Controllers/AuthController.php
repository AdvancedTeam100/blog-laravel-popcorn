<?php

namespace App\Http\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'loginOTP', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'ユーザーIDまたはパスワードが間違っています。'], 401);
        }

        if (auth()->user()->status == '2' || auth()->user()->status == '3') {
            return response()->json(['message' => 'あなたのアカウントは一時停止されました。'], 401);
        }

        $user = auth()->user();

        if($user->role_id == '1') {
            $credentials = [
                'user_id' => auth()->user()->user_id,
                'role_id' => auth()->user()->role_id,
                'name' => auth()->user()->name
                ];
    
            $usertoken = JWT::encode($credentials, env("JWT_SECRET"), 'HS256');
            return $this->createNewToken($token, $usertoken);
        }


        if (auth()->user()->google2fa_secret && auth()->user()->google_logged == 'true') {

            return response()->json([
                'login_status' => 'OTP',
                'google2fa_secret' => auth()->user()->google2fa_secret,
                'qr_codeurl' => auth()->user()->qr_codeurl,
            ]);
        } else {
            // Initialise the 2FA class
            $google2fa = app('pragmarx.google2fa');

            // Add the secret key to the registration data
            $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();
            $registration_data['email'] = auth()->user()->email;

            // Generate the QR image. This is the image the user will scan with their app
            // to set up two factor authentication
            $QR_CodeUrl = $google2fa->getQRCodeInline(
                config('app.name'),
                $registration_data['email'],
                $registration_data['google2fa_secret']
            );

            // Set the filename for the new SVG file
            $filename = uniqid() . '.' . 'svg';

            $public_path = public_path();

            // Save the SVG file to the public directory
            File::put($public_path . '/upload/qrcodes/' . $filename, $QR_CodeUrl);

            $user->google2fa_secret = $registration_data['google2fa_secret'];
            $user->qr_codeurl = $filename;
            $user->save();

            return response()->json([
                'login_status' => 'QRSCAN',
                'google2fa_secret' => $registration_data['google2fa_secret'],
                'qr_codeurl' => $filename,
            ]);
        }

    }

    public function loginOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

            
        if ($validator->fails()) {
            return response()->json(['message' => 'デジタルコードを入力してください'], 422);
        }

        $credentials = $request->only('user_id', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'ユーザーIDまたはパスワードが間違っています。'], 401);
        }
    
        $user = Auth::user();


        // Verify the OTP using the user's secret key
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);
        
        if ($valid) {
            $user->google_logged = 'true';
            $user->save();
            $credentials = [
            'user_id' => auth()->user()->user_id,
            'role_id' => auth()->user()->role_id,
            'name' => auth()->user()->name
            ];

        $usertoken = JWT::encode($credentials, env("JWT_SECRET"), 'HS256');
        return $this->createNewToken($token, $usertoken);

        } else {
            return response()->json(['message' => '正しいデジタルコードを入力してください'], 400);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'ユーザーが正常に登録されました',
            'user' => $user,
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'ユーザーは正常にサインアウトしました']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {

        $credentials = [
            'user_id' => auth()->user()->user_id,
            'role_id' => auth()->user()->role_id,
            'name' => auth()->user()->name,
        ];
        $usertoken = JWT::encode($credentials, env("JWT_SECRET"), 'HS256');

        return $this->createNewToken(auth()->refresh(), $usertoken);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
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
            'login_status' => 'LOGGED',
            'access_token' => $token,
            'user_token' => $usertoken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

}
