<?php

namespace Laravel\Spark\Http\Controllers\Settings\Billing;

use Laravel\Spark\Spark;
use Illuminate\Http\Response;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Contracts\Repositories\LocalInvoiceRepository;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled as TeamSubscriptionCancelled;
use Laravel\Spark\TeamSubscription;

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

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    /**
     * Handle customer subscription updated.
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
        })->each(function (TeamSubscription $subscription) use ($data) {

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
                    $subscription->status = 'incomplete';
                } else {
                    $subscription->status = 'active';
                }
            }

            $subscription->save();
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
