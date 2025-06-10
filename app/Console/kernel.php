<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('backup:database')
                ->dailyAt('01:00')
                ->appendOutputTo(storage_path('logs/backup.log'));

        $schedule->command('cache:clear')->daily();

        $schedule->command('model:prune', [
            '--model' => [App\Models\Alimento::class],
            '--days' => 30,
        ])->daily();
    }

    
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}