<?php

namespace Laravel\Spark\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Laravel\Spark\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the settings dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $setupIntent = $request->user()->createSetupIntent();

        return view('spark::settings', [
            'clientSecret' => $setupIntent->client_secret
        ]);
    }
}
