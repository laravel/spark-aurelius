<?php

namespace Laravel\Spark\Http\Controllers\Settings\Teams;

use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\UpdateTeamPhoto;
use Laravel\Spark\Http\Requests\Settings\Teams\UpdateTeamPhotoRequest;

class TeamPhotoController extends Controller
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
     * Update the given team's photo.
     *
     * @param  \Laravel\Spark\Http\Requests\Settings\Teams\UpdateTeamPhotoRequest  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeamPhotoRequest $request, $team)
    {
        $this->interaction(
            $request, UpdateTeamPhoto::class,
            [$team, $request->all()]
        );
    }
}
