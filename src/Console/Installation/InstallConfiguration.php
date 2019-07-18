<?php

namespace Laravel\Spark\Console\Installation;

class InstallConfiguration
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

        $this->command->line('Updating Configuration Values: <info>✔</info>');
    }

    /**
     * Install the components.
     *
     * @return void
     */
    public function install()
    {
        copy(SPARK_STUB_PATH.'/config/auth.php', config_path('auth.php'));
    }
}
