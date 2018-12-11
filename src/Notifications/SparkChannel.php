<?php

namespace Laravel\Spark\Notifications;

use RuntimeException;
use Laravel\Spark\Team;
use Illuminate\Notifications\Notification;
use Laravel\Spark\Contracts\Repositories\NotificationRepository;

class SparkChannel
{
    /**
     * The notifications repository implementation.
     *
     * @var \Laravel\Spark\Contracts\Repositories\NotificationRepository
     */
    private $notifications;

    /**
     * Create a new spark channel instance.
     *
     * @param  \Laravel\Spark\Contracts\Repositories\NotificationRepository  $notifications
     * @return void
     */
    public function __construct(NotificationRepository $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notifiable, 'routeNotificationForSpark')) {
            $notifiable = $notifiable->routeNotificationForSpark() ? $notifiable->routeNotificationForSpark() : $notifiable;
        }
        
        $users = $notifiable instanceof Team ? $notifiable->users : [$notifiable];

        $data = $this->getData($notifiable, $notification);

        foreach ($users as $user) {
            $this->notifications->create($user, $data);
        }
    }

    /**
     * Get the data for the notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     *
     * @throws RuntimeException
     */
    protected function getData($notifiable, Notification $notification)
    {
        $message = $notification->toSpark($notifiable);

        return [
            'icon' => $message->icon,
            'body' => $message->body,
            'from' => $message->from,
            'action_text' => $message->actionText,
            'action_url' => $message->actionUrl,
        ];
    }
}
