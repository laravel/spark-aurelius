<?php

namespace Laravel\Spark\Http\Controllers\Kiosk;

use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Http\Controllers\Controller;
use Laravel\Spark\Contracts\Repositories\UserRepository;
use Laravel\Spark\Contracts\Repositories\TeamRepository;
use Laravel\Spark\Contracts\Repositories\PerformanceIndicatorsRepository;

class ProfileController extends Controller
{
    /**
     * The performance indicators repository instance.
     *
     * @var PerformanceIndicatorsRepository
     */
    protected $indicators;

    /**
     * Create a new controller instance.
     *
     * @param  PerformanceIndicatorsRepository  $indicators
     * @return void
     */
    public function __construct(PerformanceIndicatorsRepository $indicators)
    {
        $this->indicators = $indicators;

        $this->middleware('auth');
        $this->middleware('dev');
    }

    /**
     * Get the user to be displayed on the user profile screen.
     *
     * @param  Request  $request
     * @param  string  $id
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $user = Spark::call(UserRepository::class.'@find', [$id]);

        return response()->json([
            'user' => $user,
            'revenue' => $this->indicators->totalRevenueForUser($user),
        ]);
    }

    /**
     * Get the team to be displayed on the team profile screen.
     *
     * @param  Request  $request
     * @param  string  $id
     * @return Response
     */
    public function showTeam(Request $request, $id)
    {
        $team = Spark::call(TeamRepository::class.'@find', [$id]);

        return response()->json([
            'team' => $team,
            'revenue' => $this->indicators->totalRevenueForTeam($team),
        ]);
    }
}
