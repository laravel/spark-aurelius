<?php

namespace Laravel\Spark;

use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Concerns\Prorates;
use Laravel\Cashier\Exceptions\SubscriptionUpdateFailure;
use Stripe\SubscriptionItem as StripeSubscriptionItem;

/**
 * @property \Laravel\Cashier\Subscription|null $subscription
 */
class TeamSubscriptionItem extends Model
{
    use Prorates;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_subscription_items';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the subscription that the item belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscription()
    {
        return $this->belongsTo(TeamSubscription::class, 'subscription_id');
    }

    /**
     * Swap the subscription item to a new Stripe plan.
     *
     * @param  string  $plan
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function swap($plan, $options = [])
    {
        if ($this->subscription->incomplete()) {
            throw SubscriptionUpdateFailure::incompleteSubscription($this->subscription);
        }

        $options = array_merge([
            'plan' => $plan,
            'quantity' => $this->quantity,
            'proration_behavior' => $this->prorateBehavior(),
            'tax_rates' => $this->subscription->getPlanTaxRatesForPayload($plan),
        ], $options);

        $item = StripeSubscriptionItem::update(
            $this->stripe_id,
            $options,
            $this->subscription->owner->stripeOptions()
        );

        $this->fill([
            'stripe_plan' => $plan,
            'quantity' => $item->quantity,
        ])->save();

        if ($this->subscription->hasSinglePlan()) {
            $this->subscription->fill([
                'stripe_plan' => $plan,
                'quantity' => $item->quantity,
            ])->save();
        }

        return $this;
    }

    /**
     * Swap the subscription item to a new Stripe plan, and invoice immediately.
     *
     * @param  string  $plan
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function swapAndInvoice($plan, $options = [])
    {
        $item = $this->swap($plan, $options);

        $this->subscription->invoice();

        return $item;
    }

    /**
     * Get the subscription as a Stripe subscription item object.
     *
     * @return StripeSubscriptionItem
     */
    public function asStripeSubscriptionItem()
    {
        return StripeSubscriptionItem::retrieve(
            $this->stripe_id,
            $this->subscription->owner->stripeOptions()
        );
    }
}
