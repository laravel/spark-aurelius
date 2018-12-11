<?php

namespace Laravel\Spark\Http\Controllers;

use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Contracts\Repositories\TeamRepository;

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
     * Get all of the teams for the current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        return Spark::interact(
            TeamRepository::class.'@forUser', [$request->user()]
        );
    }

    /**
     * Get the current team for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function current(Request $request)
    {
        abort_unless($request->user()->current_team_id, 404);

        return Spark::interact(TeamRepository::class.'@find', [
            $request->user()->current_team_id
        ]);
    }

    /**
     * Get the team matching the given ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $teamId
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $teamId)
    {
        $team = Spark::interact(TeamRepository::class.'@find', [$teamId]);

        abort_unless($request->user()->onTeam($team), 404);

        if ($request->user()->ownsTeam($team)) {
            $team->load('subscriptions');

            $team->shouldHaveOwnerVisibility();
        }

        return $team;
    }

    /**
     * Switch the current team the user is viewing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function switchCurrentTeam(Request $request, $team)
    {
        abort_unless($request->user()->onTeam($team), 404);

        $request->user()->switchToTeam($team);

        return back();
    }
}
