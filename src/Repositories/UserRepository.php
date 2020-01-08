<?php

namespace Laravel\Spark\Repositories;

use Carbon\Carbon;
use Laravel\Spark\Spark;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Spark\Events\PaymentMethod\VatIdUpdated;
use Laravel\Spark\Events\PaymentMethod\BillingAddressUpdated;
use Laravel\Spark\Contracts\Repositories\UserRepository as UserRepositoryContract;

class UserRepository implements UserRepositoryContract
{
    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (Auth::check()) {
            return $this->find(Auth::id())->shouldHaveSelfVisibility();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $user = Spark::user()->find($id);

        return $user ? $this->loadUserRelationships($user) : null;
    }

    /**
     * Load the relationships for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    protected function loadUserRelationships($user)
    {
        $user->load('subscriptions');

        if (Spark::usesTeams()) {
            $user->load(['ownedTeams.subscriptions', 'teams.subscriptions']);

            $user->currentTeam();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $excludeUser = null)
    {
        $search = Spark::user()->with('subscriptions');

        // If a user to exclude was passed to the repository, we will exclude their User
        // ID from the list. Typically we don't want to show the current user in the
        // search results and only want to display the other users from the query.
        if ($excludeUser) {
            $search->where(Spark::user()->getKeyName(), '<>', $excludeUser->id);
        }

        $query = strtolower($query);
        return $search->where(function ($search) use ($query) {
            $search->whereRaw('lower(email) like (?)', ["%{$query}%"])
                ->orWhereRaw('lower(name) like (?)', ["%{$query}%"]);
        })->get();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $user = Spark::user();

        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'last_read_announcements_at' => Carbon::now(),
            'trial_ends_at' => Spark::onlyTeamPlans() ? null : Carbon::now()->addDays(Spark::trialDays()),
        ])->save();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function updateBillingAddress($user, array $data)
    {
        $user->forceFill([
            'card_country' => Arr::get($data, 'card_country', $user->card_country),
            'billing_address' => Arr::get($data, 'address'),
            'billing_address_line_2' => Arr::get($data, 'address_line_2'),
            'billing_city' => Arr::get($data, 'city'),
            'billing_state' => Arr::get($data, 'state'),
            'billing_zip' => Arr::get($data, 'zip'),
            'billing_country' => Arr::get($data, 'country'),
        ])->save();

        event(new BillingAddressUpdated($user));
    }

    /**
     * {@inheritdoc}
     */
    public function updateVatId($user, $vatId)
    {
        $user->forceFill(['vat_id' => $vatId])->save();

        event(new VatIdUpdated($user));
    }
}
