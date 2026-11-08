<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        // SECURITY: the reset-link endpoint had no rate limit (mail bombing /
        // enumeration at scale).
        $this->middleware('throttle:5,1')->only('sendResetLinkEmail');
    }

    /**
     * SECURITY: the stock behaviour answered "We can't find a user with that email
     * address" for unknown emails (account enumeration). Every outcome — unknown
     * user or per-user throttle — now returns the same response as a success.
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
    }
}
