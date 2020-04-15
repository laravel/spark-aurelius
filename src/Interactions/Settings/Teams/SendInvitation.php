<?php

namespace Laravel\Spark\Interactions\Settings\Teams;

use Ramsey\Uuid\Uuid;
use Laravel\Spark\Spark;
use Illuminate\Support\Str;
use Laravel\Spark\Invitation;
use Illuminate\Support\Facades\Mail;
use Laravel\Spark\Events\Teams\UserInvitedToTeam;
use Laravel\Spark\Events\Teams\GuestInvitedToTeam;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\SendInvitation as Contract;

class SendInvitation implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function handle($team, $email, $role)
    {
        $invitedUser = Spark::user()->where('email', $email)->first();

        $role = array_key_exists($role, Spark::roles()) ? $role : Spark::defaultRole();

        $this->emailInvitation(
            $invitation = $this->createInvitation($team, $email, $invitedUser, $role)
        );

        if ($invitedUser) {
            event(new UserInvitedToTeam($team, $invitedUser));
        } else {
            event(new GuestInvitedToTeam($team, $invitation));
        }

        return $invitation;
    }

    /**
     * E-mail the given invitation instance.
     *
     * @param  \Laravel\Spark\Invitation  $invitation
     * @return void
     */
    protected function emailInvitation($invitation)
    {
        Mail::send($this->view($invitation), compact('invitation'), function ($m) use ($invitation) {
            $m->to($invitation->email)->subject(__('New Invitation!'));
        });
    }

    /**
     * Create a new invitation instance.
     *
     * @param  \Laravel\Spark\Team  $team
     * @param  string  $email
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $invitedUser
     * @return \Laravel\Spark\Invitation
     */
    protected function createInvitation($team, $email, $invitedUser, $role)
    {
        return $team->invitations()->create([
            'id' => Uuid::uuid4(),
            'user_id' => $invitedUser ? $invitedUser->id : null,
            'role' => $role,
            'email' => $email,
            'token' => Str::random(40),
        ]);
    }

    /**
     * Get the proper e-mail view for the given invitation.
     *
     * @param  \Laravel\Spark\Invitation  $invitation
     * @return string
     */
    protected function view(Invitation $invitation)
    {
        return $invitation->user_id
                        ? 'spark::settings.teams.emails.invitation-to-existing-user'
                        : 'spark::settings.teams.emails.invitation-to-new-user';
    }
}
