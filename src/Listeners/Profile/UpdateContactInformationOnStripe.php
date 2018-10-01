<?php

namespace Laravel\Spark\Listeners\Profile;

class UpdateContactInformationOnStripe
{
    /**
     * Handle the event.
     *
     * @param  \Laravel\Spark\Events\Profile\ContactInformationUpdated  $event
     */
    public function handle($event)
    {
        if ($event->user->hasBillingProvider()) {
            $customer = $event->user->asStripeCustomer();

            $customer->email = $event->user->email;

            $customer->save();
        }

        if (isset($event->user->ownedTeams)) {
            $this->updateForTeams($event->user);
        }
    }

    /**
     * Update contact information for owned teams.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    private function updateForTeams($user)
    {
        foreach ($user->ownedTeams as $team) {
            if ($team->hasBillingProvider()) {
                $customer = $team->asStripeCustomer();

                $customer->email = $user->email;

                $customer->save();
            }
        }
    }
}
