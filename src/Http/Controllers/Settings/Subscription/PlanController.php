<?php

namespace Laravel\Spark\Http\Controllers\Settings\Subscription;

use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Spark\Contracts\Interactions\Subscribe;
use Laravel\Spark\Events\Subscription\SubscriptionUpdated;
use Laravel\Spark\Events\Subscription\SubscriptionCancelled;
use Laravel\Spark\Http\Requests\Settings\Subscription\UpdateSubscriptionRequest;
use Laravel\Spark\Contracts\Interactions\Settings\PaymentMethod\UpdatePaymentMethod;
use Laravel\Spark\Contracts\Http\Requests\Settings\Subscription\CreateSubscriptionRequest;

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
     * Create the subscription for the user.
     *
     * @param  \Laravel\Spark\Contracts\Http\Requests\Settings\Subscription\CreateSubscriptionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSubscriptionRequest $request)
    {
        $plan = Spark::plans()->where('id', $request->plan)->first();

        try{
            if ($request->stripe_payment_method) {
                Spark::interact(UpdatePaymentMethod::class, [
                    $request->user(), $request->all(),
                ]);
            }

            Spark::interact(Subscribe::class, [
                $request->user(), $plan, false, $request->all()
            ]);
        } catch (IncompletePayment $e) {
            return response()->json([
                'paymentId' => $e->payment->id
            ], 400);
        }
    }

    /**
     * Update the subscription for the user.
     *
     * @param  \Laravel\Spark\Http\Requests\Settings\Subscription\UpdateSubscriptionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSubscriptionRequest $request)
    {
        $plan = Spark::plans()->where('id', $request->plan)->first();

        // This method is used both for updating subscriptions and for resuming cancelled
        // subscriptions that are still within their grace periods as this swap method
        // will be used for either of these situations without causing any problems.
        if ($plan->price === 0) {
            return $this->destroy($request);
        } else {
            $subscription = $request->user()->subscription();

            if (Spark::chargesUsersPerTeam() || Spark::chargesUsersPerSeat()) {
                $subscription->forceFill([
                    'quantity' => Spark::seatsCount($request->user())
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
     * Cancel the user's subscription.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->user()->subscription()->cancel();

        event(new SubscriptionCancelled($request->user()->fresh()));
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
