<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{

    use ResetsPasswords;

    protected function sendResetResponse(\Illuminate\Http\Request $request, $response)
    {
        return response()->json(['status' => trans($response)], 200);
    }

    protected function sendResetFailedResponse(\Illuminate\Http\Request $request, $response)
    {
        return response()->json(['email' => trans($response)], 401);
    }
}
