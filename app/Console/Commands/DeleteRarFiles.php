<?php

namespace App\Console\Commands;

use App\Services\EnebaService;
use App\Services\FileService;
use App\Services\GamivoService;
use App\Services\KinguinService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DeleteRarFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-rar-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(FileService $fileService)
    {
        $channels = [
            "orderZip",
        ];

        try {
            foreach ($channels as $channel) {
                $fileService->deleteFiles($channel);
            }
        } catch (\Exception $exception) {
            Log::channel('test_log')->info('rarlar silinirken hata oluştu');
            Log::channel('test_log')->info($exception->getMessage());
            Log::channel('test_log')->info("File : " . $exception->getMessage());
            Log::channel('test_log')->info("Line : " . $exception->getLine());
        }
    }


}
