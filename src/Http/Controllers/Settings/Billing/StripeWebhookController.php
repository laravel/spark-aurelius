<?php

namespace Laravel\Spark\Http\Controllers\Settings\Billing;

use Laravel\Spark\Spark;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Laravel\Spark\Subscription;
use Laravel\Spark\TeamSubscription;
use Laravel\Spark\Events\Subscription\UserSubscribed;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Laravel\Spark\Events\Subscription\SubscriptionUpdated;
use Laravel\Spark\Events\Teams\Subscription\TeamSubscribed;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Contracts\Repositories\LocalInvoiceRepository;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionUpdated as TeamSubscriptionUpdated;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled as TeamSubscriptionCancelled;

class StripeWebhookController extends WebhookController
{
    use SendsInvoiceNotifications;

    /**
     * Handle customer subscription updated.
     *
     * @param  array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $user = $this->getUserByStripeId(
            $payload['data']['object']['customer']
        );

        if (is_null($user)) {
            return $this->handleTeamSubscriptionUpdated($payload);
        }

        return $this->handleUserSubscriptionUpdated($user, $payload);
    }

    /**
     * Handle user subscription updated.
     *
     * @param  \Laravel\Cashier\Billable $user
     * @param  array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleUserSubscriptionUpdated($user, array $payload)
    {
        $data = $payload['data']['object'];

        $user->subscriptions->filter(function (Subscription $subscription) use ($data) {
            return $subscription->stripe_id === $data['id'];
        })->each(function (Subscription $subscription) use ($data, $user) {
            if (isset($data['status']) && $data['status'] === 'incomplete_expired') {
                $subscription->delete();

                return;
            }

            // Quantity...
            if (isset($data['quantity'])) {
                $subscription->quantity = $data['quantity'];
            }

            // Plan...
            if (isset($data['plan']['id'])) {
                $subscription->stripe_plan = $data['plan']['id'];
            }

            // Trial ending date...
            if (isset($data['trial_end'])) {
                $trial_ends = Carbon::createFromTimestamp($data['trial_end']);

                if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
                    $subscription->trial_ends_at = $trial_ends;
                }
            }

            // Cancellation date...
            if (isset($data['cancel_at_period_end'])) {
                if ($data['cancel_at_period_end']) {
                    $subscription->ends_at = $subscription->onTrial()
                        ? $subscription->trial_ends_at
                        : Carbon::createFromTimestamp($data['current_period_end']);
                } else {
                    $subscription->ends_at = null;
                }
            }

            // Status...
            if (isset($data['status'])) {
                $subscription->stripe_status = $data['status'];
            }

            $subscription->save();

            if (! $user->current_billing_plan) {
                event(new UserSubscribed(
                    $user, Spark::plans()->where('id', $subscription->stripe_plan)->first(), false
                ));
            } elseif(isset($payload['data']['previous_attributes']['items'])) {
                event(new SubscriptionUpdated($user));
            }
        });
    }

    /**
     * Handle team subscription updated.
     *
     * @param  array $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleTeamSubscriptionUpdated(array $payload)
    {
        $team = Spark::team()->where(
            'stripe_id', $payload['data']['object']['customer']
        )->first();

        $data = $payload['data']['object'];

        $team->subscriptions->filter(function (TeamSubscription $subscription) use ($data) {
            return $subscription->stripe_id === $data['id'];
        })->each(function (TeamSubscription $subscription) use ($payload, $data, $team) {

            // Quantity...
            if (isset($data['quantity'])) {
                $subscription->quantity = $data['quantity'];
            }

            // Plan...
            if (isset($data['plan']['id'])) {
                $subscription->stripe_plan = $data['plan']['id'];
            }

            // Trial ending date...
            if (isset($data['trial_end'])) {
                $trial_ends = Carbon::createFromTimestamp($data['trial_end']);

                if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
                    $subscription->trial_ends_at = $trial_ends;
                }
            }

            // Cancellation date...
            if (isset($data['cancel_at_period_end'])) {
                if ($data['cancel_at_period_end']) {
                    $subscription->ends_at = $subscription->onTrial()
                        ? $subscription->trial_ends_at
                        : Carbon::createFromTimestamp($data['current_period_end']);
                } else {
                    $subscription->ends_at = null;
                }
            }

            // Status...
            if (isset($data['status'])) {
                if (in_array($data['status'], ['incomplete', 'incomplete_expired'])) {
                    $subscription->stripe_status = 'incomplete';
                } else {
                    $subscription->stripe_status = 'active';
                }
            }

            $subscription->save();

            if (! $team->current_billing_plan) {
                event(new TeamSubscribed(
                    $team, Spark::teamPlans()->where('id', $subscription->stripe_plan)->first()
                ));
            } elseif(isset($payload['data']['previous_attributes']['items'])) {
                event(new TeamSubscriptionUpdated($team));
            }
        });
    }

    /**
     * Handle a successful invoice payment from a Stripe subscription.
     *
     * By default, this e-mails a copy of the invoice to the customer.
     *
     * @param  array  $payload
     * @return \Illuminate\Http\Response
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $user = $this->getUserByStripeId(
            $payload['data']['object']['customer']
        );

        if (is_null($user)) {
            return $this->teamInvoicePaymentSucceeded($payload);
        }

        $invoice = $user->findInvoice($payload['data']['object']['id']);

        app(LocalInvoiceRepository::class)->createForUser($user, $invoice);

        $this->sendInvoiceNotification(
            $user, $invoice
        );

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a successful invoice payment from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Illuminate\Http\Response
     */
    protected function teamInvoicePaymentSucceeded(array $payload)
    {
        $team = Spark::team()->where(
            'stripe_id', $payload['data']['object']['customer']
        )->first();

        if (is_null($team)) {
            return;
        }

        $invoice = $team->findInvoice($payload['data']['object']['id']);

        app(LocalInvoiceRepository::class)->createForTeam($team, $invoice);

        $this->sendInvoiceNotification(
            $team, $invoice
        );

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Illuminate\Http\Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if (! $user) {
            return $this->teamSubscriptionDeleted($payload);
        }

        event(new SubscriptionCancelled(
            $this->getUserByStripeId($payload['data']['object']['customer']))
        );

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Illuminate\Http\Response
     */
    protected function teamSubscriptionDeleted(array $payload)
    {
        $team = Spark::team()->where(
            'stripe_id', $payload['data']['object']['customer']
        )->first();

        if ($team) {
            $team->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });
        } else {
            return new Response('Webhook Handled', 200);
        }

        event(new TeamSubscriptionCancelled($team));

        return new Response('Webhook Handled', 200);
    }
}
