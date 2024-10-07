<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetApplication extends Command
{
    protected $signature = 'app:reset';
    protected $description = 'Reset the application to its initial state';

    public function handle()
    {
        $this->call('migrate:fresh', ['--seed' => true]);
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        // Add any custom reset logic here

        $this->info('Application has been reset successfully.');
    }
}
