<?php

namespace App\Console\Commands;

use App\Services\FileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteLogFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-log-files';

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
        
        $channels = collect(array_keys(config('filesystems.disks')))->filter(fn($item) => !in_array($item, [
            'local', 'image', 'public', 's3'
        ]))->toArray();

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
