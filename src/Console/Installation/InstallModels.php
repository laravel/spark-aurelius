<?php

namespace Laravel\Spark\Console\Installation;

class InstallModels
{
    /**
     * The console command instance.
     *
     * @var \Illuminate\Console\Command  $command
     */
    protected $command;

    /**
     * Create a new installer instance.
     *
     * @param  \Illuminate\Console\Command  $command
     * @return void
     */
    public function __construct($command)
    {
        $this->command = $command;

        $this->command->line('Installing Eloquent Models: <info>✔</info>');
    }

    /**
     * Install the components.
     *
     * @return void
     */
    public function install()
    {
        copy($this->getUserModel(), app_path('Models/User.php'));

        copy(SPARK_STUB_PATH.'/app/Models/Team.php', app_path('Models/Team.php'));
    }

    /**
     * Get the path to the proper User model stub.
     *
     * @return string
     */
    protected function getUserModel()
    {
        return $this->command->option('team-billing')
                            ? SPARK_STUB_PATH.'/app/Models/TeamUser.php'
                            : SPARK_STUB_PATH.'/app/Models/User.php';
    }
}
