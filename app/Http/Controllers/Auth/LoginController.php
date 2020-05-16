<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    public function attemptLogin(Request $request)
    {
        // ログイン認証情報に基づいてユーザーにトークンを発行しようとする
        $token = $this->guard()->attempt($this->credentials($request));

        if ( ! $token) {
            return false;
        }

        // 認証ユーザーを取得する
        $user = $this->guard()->user();

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return false;
        }

        // userのtokenをセット
        $this->guard()->setToken($token);

        return true;
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        // authentication guardからトークンを取得する(JWT)
        $token = (string)$this->guard()->getToken();

        // トークンの有効期限を抽出する
        $expiration = $this->guard()->getPayload()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = $this->guard()->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return response()->json([
                "verification" => "メールアカウントを確認する必要があります"
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => "認証情報が正しくありません"
        ]);
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message' => 'ログアウトしました']);
    }

}
