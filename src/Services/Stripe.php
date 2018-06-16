<?php

namespace Laravel\Spark\Services;

use Stripe\Token as StripeToken;

class Stripe
{
    /**
     * Get the country code for the given Stripe token.
     *
     * @param  string  $token
     * @return string
     */
    public function countryForToken($token)
    {
        return StripeToken::retrieve(
            $token, config('services.stripe.secret')
        )->card->country;
    }
}
