<?php

namespace App\Console;

use App\Events\BackupDb;
use App\Jobs\DeleteBackFiles;
use App\Mail\BackupMail;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Console\PruneCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:get-daily-currency')->dailyAt('15:00');
//        $schedule->command('app:move-status-histories')->monthlyOn(1, '01:00');;
        $schedule->command('app:delete-rar-files')->dailyAt("02:00");
        $schedule->command('app:delete-log-files')->dailyAt("03:00");
        $schedule->command('app:clean-activity-log')->dailyAt('02:00');
        $schedule->command('sanctum:prune-expired --hours=24')->daily();
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command(PruneCommand::class)->daily();
        $schedule->command('app:make-reject-automatically')->everyThreeHours();

//        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->everySixHours()
            ->onFailure(function () {
                Mail::to(['nurullah.demirel@5deniz.com', 'gazi.hatas@5deniz.com'])->send(new BackupMail(false));;
            })
            ->onSuccess(function () {
                dispatch(new BackupDb());
            });


    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
