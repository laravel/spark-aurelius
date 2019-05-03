<?php

namespace Laravel\Spark\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Spark\Spark;

class VerificationController extends Controller
{
    use VerifiesEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');

        $this->redirectTo = Spark::afterLoginRedirect();
    }

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
                        ? redirect($this->redirectPath())
                        : view('spark::auth.verify');
    }
}
