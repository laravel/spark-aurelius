<?php

namespace Laravel\Spark\Interactions;

use Laravel\Spark\Spark;
use Illuminate\Support\Arr;
use Laravel\Spark\Events\Subscription\UserSubscribed;
use Laravel\Spark\Contracts\Repositories\UserRepository;
use Laravel\Spark\Contracts\Interactions\Subscribe as Contract;

class SubscribeUsingStripe implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($user, $plan, $fromRegistration, array $data)
    {
        $user->subscriptions()->whereIn('stripe_status', ['incomplete', 'past_due'])->each(function ($subscription) {
            $subscription->cancelNow();

            $subscription->delete();
        });

        $subscription = $user->newSubscription('default', $plan->id);

        // Here we will check if we need to skip trial or set trial days on the subscription
        // when creating it on the provider. By default, we will skip the trial when this
        // interaction isn't from registration since they have already usually trialed.
        if (! $fromRegistration && $user->hasEverSubscribedTo('default', $plan->id)) {
            $subscription->skipTrial();
        } elseif ($plan->trialDays > 0) {
            $subscription->trialDays($plan->trialDays);
        }

        if (isset($data['coupon'])) {
            $subscription->withCoupon($data['coupon']);
        }

        // Next, we need to check if this application is storing billing addresses and if so
        // we will update the billing address in the database so that any tax information
        // on the user will be up to date via the taxPercentage method on the billable.
        if (Spark::collectsBillingAddress()) {
            Spark::call(
                UserRepository::class.'@updateBillingAddress',
                [$user, $data]
            );
        }

        // If this application collects European VAT, we will store the VAT ID that was sent
        // with the request. It is used to determine if the VAT should get charged at all
        // when billing the customer. When it is present, VAT is not typically charged.
        if (Spark::collectsEuropeanVat()) {
            Spark::call(
                UserRepository::class.'@updateVatId',
                [$user, Arr::get($data, 'vat_id')]
            );
        }

        if (Spark::chargesUsersPerTeam() || Spark::chargesUsersPerSeat()) {
            $subscription->quantity(Spark::seatsCount($user, $plan->id));
        }

        // Here we will create the actual subscription on the service and fire off the event
        // letting other listeners know a user has subscribed, which will allow any hooks
        // to fire that need to send the subscription data to any external metrics app.
        $subscription->create();

        event(new UserSubscribed(
            $user = $user->fresh(), $plan, $fromRegistration
        ));

        return $user;
    }
}
