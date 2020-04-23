<?php

namespace Laravel\Spark;

use Laravel\Cashier\Subscription as CashierSubscription;

class TeamSubscription extends CashierSubscription
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_subscriptions';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['provider_plan'];

    /**
     * Get the team that owns the subscription.
     */
    public function team()
    {
        return $this->user();
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(Spark::teamModel(), 'team_id');
    }

    /**
     * Get the subscription items related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(TeamSubscriptionItem::class, 'subscription_id');
    }

    /**
     * Get the "provider_plan" attribute from the model.
     *
     * @return string
     */
    public function getProviderPlanAttribute()
    {
        return $this->stripe_plan;
    }
}
