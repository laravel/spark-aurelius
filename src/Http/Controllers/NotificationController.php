<?php

namespace Laravel\Spark\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Spark\Notification;
use Laravel\Spark\Contracts\Repositories\NotificationRepository;
use Laravel\Spark\Contracts\Repositories\AnnouncementRepository;

class NotificationController extends Controller
{
    /**
     * The announcements repository.
     *
     * @var \Laravel\Spark\Contracts\Repositories\AnnouncementRepository
     */
    protected $announcements;

    /**
     * The notifications repository.
     *
     * @var \Laravel\Spark\Contracts\Repositories\NotificationRepository
     */
    protected $notifications;

    /**
     * Create a new controller instance.
     *
     * @param  \Laravel\Spark\Contracts\Repositories\AnnouncementRepository  $announcements
     * @param  \Laravel\Spark\Contracts\Repositories\NotificationRepository  $notifications
     * @return void
     */
    public function __construct(AnnouncementRepository $announcements,
                                NotificationRepository $notifications)
    {
        $this->announcements = $announcements;
        $this->notifications = $notifications;

        $this->middleware('auth');
    }

    /**
     * Get the recent notifications and announcements for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recent(Request $request)
    {
        return response()->json([
            'announcements' => $this->announcements->recent()->toArray(),
            'notifications' => $this->notifications->recent($request->user())->toArray(),
        ]);
    }

    /**
     * Mark the given notifications as read.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Request $request)
    {
        Notification::whereIn('id', $request->notifications)->whereUserId($request->user()->id)->update(['read' => 1]);
    }
}
