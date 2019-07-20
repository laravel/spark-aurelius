<?php

namespace Laravel\Spark\Interactions\Settings\Teams;

use Laravel\Spark\Spark;
use Laravel\Spark\Events\Teams\TeamMemberAdded;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\AddTeamMember as Contract;

class AddTeamMember implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($team, $user, $role = null)
    {
        $team->users()->attach($user, ['role' => $role ?: Spark::defaultRole()]);

        event(new TeamMemberAdded($team, $user));

        try {
            if (Spark::chargesTeamsPerMember() && $team->subscription() &&
                $team->users()->count() > 1
            ) {
                $team->addSeat();
            }
        } catch (IncompletePayment $e) {
            // We'll do nothing since members are added by accepting an invitation so
            // there's no immediate action the invited user can take. We'll leave it
            // to the team owner to notice the subscription has a pending payment.
        }

        return $team;
    }
}
