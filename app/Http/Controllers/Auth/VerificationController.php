<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;

// use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request, User $user)
    {
        // URLが有効なURLかどうかのチェック
        if(! URL::hasValidSignature($request)){
            return response()->json(["errors" => [
                "message" => "無効な確認リンクです"
            ]], 422);
        }

        // ユーザーがすでに確認されたアカウントかどうか
        if($user->hasVerifiedEmail()){
            return response()->json(["errors" => [
                "message" => "メールアドレスは確認済みです"
            ]], 422);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'メールを確認しました'], 200);
    }

    public function resend(Request $request)
    {
        $this->validate($request, [
            'email' => ['email', 'required']
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(["error" => [
                "email" => "このメールアドレスのユーザーは見つかりませんでした"
                ]], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(["error" => [
                "message" => "メールアドレスが確認済みです"
            ]], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['status' => "確認リンクを送信しました"]);
    }
}
