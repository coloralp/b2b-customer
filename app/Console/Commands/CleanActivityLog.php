<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class CleanActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-activity-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'activity_log tablsunda oluşturulma tarihi 14 günü geçen dataları temizler';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = now()->subDays(14);

        $deletedCount = DB::table('activity_log')->where('created_at', '<', $date)->count();
        DB::table('activity_log')->where('created_at', '<', $date)->delete();

        $startDate = $date->format('d-m-Y');
        $endDate = now()->format('d-m-Y');

        $this->info($deletedCount . ' adet activity_log verisi silindi (' . $startDate . ' ile ' . $endDate . ' arasında).');

    }
}
