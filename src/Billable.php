<?php

namespace Laravel\Spark;

use Mpociot\VatCalculator\VatCalculator;
use Laravel\Cashier\Billable as CashierBillable;

trait Billable
{
    use CashierBillable;

    /**
     * Determine if the user is connected to any payment provider.
     *
     * @return bool
     */
    public function hasBillingProvider()
    {
        return $this->stripe_id || $this->braintree_id;
    }

    /**
     * Get all of the subscriptions for the user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the Spark plan that corresponds with the given subscription.
     *
     * If they are not subscribed and a free plan exists, that will be returned.
     *
     * @param  string  $subscription
     * @return \Laravel\Spark\Plan|null
     */
    public function sparkPlan($subscription = 'default')
    {
        $subscription = $this->subscription($subscription);

        if ($subscription && $subscription->valid()) {
            return $this->availablePlans()->first(function ($value) use ($subscription) {
                return $value->id === $subscription->provider_plan;
            });
        }

        return $this->availablePlans()->first(function ($value) {
            return $value->price === 0;
        });
    }

    /**
     * Determine if the Stripe model has subscribed before.
     *
     * @param  string  $subscription
     * @param  string|null  $plan
     * @return bool
     */
    public function hasEverSubscribedTo($subscription = 'default', $plan = null)
    {
        if (is_null($subscription = $this->subscription($subscription))) {
            return false;
        }

        return $plan ? $subscription->provider_plan == $plan : true;
    }

    /**
     * Add seats to the current subscription.
     *
     * @param  int  $count
     * @param  string  $subscription
     * @return void
     */
    public function addSeat($count = 1, $subscription = 'default')
    {
        if (! $subscription = $this->subscription($subscription)) {
            return;
        }

        if ($subscription->onGracePeriod()) {
            $subscription->update([
                'quantity' => $subscription->quantity + $count
            ]);

            return;
        }

        if (Spark::prorates()) {
            $subscription->incrementQuantity($count);
        } else {
            $subscription->noProrate()->incrementQuantity($count);
        }
    }

    /**
     * Remove seats from the current subscription.
     *
     * @param  int  $count
     * @param  string  $subscription
     * @return void
     */
    public function removeSeat($count = 1, $subscription = 'default')
    {
        if (! $subscription = $this->subscription($subscription)) {
            return;
        }

        if ($subscription->onGracePeriod()) {
            $subscription->update([
                'quantity' => max(1, $subscription->quantity - $count)
            ]);

            return;
        }

        if (Spark::prorates()) {
            $subscription->decrementQuantity($count);
        } else {
            $subscription->noProrate()->decrementQuantity($count);
        }
    }

    /**
     * Update the number of seats in the current subscription.
     *
     * @param  int  $count
     * @param  string  $subscription
     * @return void
     */
    public function updateSeats($count, $subscription = 'default')
    {
        if (! $subscription = $this->subscription($subscription)) {
            return;
        }

        if ($subscription->onGracePeriod()) {
            $subscription->update([
                'quantity' => $count
            ]);

            return;
        }

        if (Spark::prorates()) {
            $subscription->updateQuantity($count);
        } else {
            $subscription->noProrate()->updateQuantity($count);
        }
    }

    /**
     * Get the available billing plans for the given entity.
     *
     * @return \Illuminate\Support\Collection
     */
    public function availablePlans()
    {
        return Spark::plans();
    }

    /**
     * Get all of the local invoices.
     */
    public function localInvoices()
    {
        return $this->hasMany(LocalInvoice::class)->orderBy('id', 'desc');
    }

    /**
     * Get the tax percentage to apply to the subscription.
     *
     * @return int
     */
    public function taxPercentage()
    {
        if (! Spark::collectsEuropeanVat()) {
            return 0;
        }

        $vatCalculator = new VatCalculator;

        $vatCalculator->setBusinessCountryCode(Spark::homeCountry());

        try {
            $isValidVAT = ! empty($this->vat_id) && $vatCalculator->isValidVATNumber($this->vat_id);
        } catch (VatCalculator\Exceptions\VATCheckUnavailableException $e) {
            $isValidVAT = false;
        }

        return $vatCalculator->getTaxRateForLocation(
            $this->billing_country, $this->billing_zip, $isValidVAT
        ) * 100;
    }
}
