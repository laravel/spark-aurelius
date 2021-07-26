<?php

namespace Laravel\Spark\Http\Controllers;

use Stripe\SetupIntent;
use Laravel\Cashier\Cashier;
use Illuminate\Http\Request;
use Laravel\Spark\Contracts\Repositories\TokenRepository;

class StripeTokenController extends Controller
{
    /**
     * Generate a new setup intent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $setupIntent = Cashier::stripe()->setupIntents->create();

        return [
            'clientSecret' => $setupIntent->client_secret
        ];
    }
}
