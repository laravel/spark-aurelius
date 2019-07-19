<?php

namespace Laravel\Spark\Http\Controllers\Settings\Teams;

use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Events\Teams\TeamDeleted;
use Laravel\Spark\Events\Teams\DeletingTeam;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\CreateTeam;

class TeamController extends Controller
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
     * Create a new team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Spark::createsAdditionalTeams()) {
            abort(404);
        }

        list($team, $paymentId) = $this->interaction($request, CreateTeam::class, [
            $request->user(), $request->all()
        ]);

        return response()->json([
            'pendingPayment' => $paymentId
        ]);
    }

    /**
     * Delete the given team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $team)
    {
        if (! $request->user()->ownsTeam($team)) {
            abort(404);
        }

        event(new DeletingTeam($team));

        $team->detachUsersAndDestroy();

        event(new TeamDeleted($team));

        if(Spark::chargesUsersPerTeam() && $team->owner->subscription() &&
            $team->owner->subscription()->quantity > 1
        ){
            $team->owner->removeSeat();
        }
    }
}
