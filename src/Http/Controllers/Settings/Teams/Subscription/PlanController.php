<?php

namespace Laravel\Spark\Http\Controllers\Settings\Teams\Subscription;

use Laravel\Spark\Team;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Spark\Contracts\Interactions\SubscribeTeam;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionUpdated;
use Laravel\Spark\Events\Teams\Subscription\SubscriptionCancelled;
use Laravel\Spark\Contracts\Interactions\Settings\PaymentMethod\UpdatePaymentMethod;
use Laravel\Spark\Http\Requests\Settings\Teams\Subscription\UpdateSubscriptionRequest;
use Laravel\Spark\Contracts\Http\Requests\Settings\Teams\Subscription\CreateSubscriptionRequest;

class PlanController extends Controller
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
     * Create the subscription for the team.
     *
     * @param  \Laravel\Spark\Contracts\Http\Requests\Settings\Teams\Subscription\CreateSubscriptionRequest  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSubscriptionRequest $request, Team $team)
    {
        try{
            if ($request->stripe_payment_method) {
                Spark::interact(UpdatePaymentMethod::class, [
                    $team, $request->all(),
                ]);
            }

            Spark::interact(SubscribeTeam::class, [
                $team, $request->plan(), false, $request->all()
            ]);
        } catch (IncompletePayment $e) {
            return response()->json([
                'paymentId' => $e->payment->id
            ], 400);
        }
    }

    /**
     * Update the subscription for the team.
     *
     * @param  \Laravel\Spark\Http\Requests\Settings\Teams\Subscription\UpdateSubscriptionRequest  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSubscriptionRequest $request, Team $team)
    {
        $plan = $request->plan();

        // This method is used both for updating subscriptions and for resuming cancelled
        // subscriptions that are still within their grace periods as this swap method
        // will be used for either of these situations without causing any problems.
        if ($plan->price === 0) {
            return $this->destroy($request, $team);
        } else {
            $subscription = $team->subscription();

            if (Spark::chargesTeamsPerMember() || Spark::chargesTeamsPerSeat()) {
                $subscription->forceFill([
                    'quantity' => Spark::teamSeatsCount($team)
                ])->save();
            }

            try {
                $this->swapPlans($request->plan, $subscription);
            } catch (IncompletePayment $e) {
                return response()->json([
                    'paymentId' => $e->payment->id
                ], 400);
            }
        }
    }

    /**
     * Cancel the team's subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Team $team)
    {
        abort_unless($request->user()->ownsTeam($team), 403);

        $team->subscription()->cancel();

        event(new SubscriptionCancelled($team->fresh()));
    }

    /**
     * Update the subscription to the given plan.
     *
     * @param  string  $plan
     * @param  \Laravel\Spark\Subscription $subscription
     * @return void
     */
    protected function swapPlans($plan, $subscription)
    {
        if (! is_null(Spark::prorationBehaviour())) {
            $subscription->setProrationBehavior(
                Spark::prorationBehaviour()
            )->swapAndInvoice($plan);
        } elseif (Spark::prorates()) {
            $subscription->swapAndInvoice($plan);
        } else {
            $subscription->noProrate()->swapAndInvoice($plan);
        }
    }
}
