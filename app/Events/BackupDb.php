<?php

namespace App\Events;

use App\Mail\BackupMail;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupDb
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        //
    }

    public function handle()
    {
        $files = Storage::files(env('APP_NAME'));
        $myFile = end($files);

        if ($myFile) {
            $client = new \Google\Client();
            $client->setClientId(config('backup-info.GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(config('backup-info.GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(config('backup-info.GOOGLE_DRIVE_REFRESH_TOKEN'));

            $service = new \Google\Service\Drive($client);

            $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, 'b2bv2');

            $myFileName = pathinfo($myFile, PATHINFO_FILENAME);

            $local_filepath = storage_path("app/CDKeyci/{$myFileName}.zip");

            $remote_filepath = 'backups' . Str::after($myFile, env('APP_NAME'));

            $localAdapter = new \League\Flysystem\Local\LocalFilesystemAdapter('/');
            $localfs = new \League\Flysystem\Filesystem($localAdapter);


            try {
                $fs = new \League\Flysystem\Filesystem($adapter);
                $time = Carbon::now();


                $fs->writeStream($remote_filepath, $localfs->readStream($local_filepath));

                $deleteFiles = collect($files)->filter(fn($item) => $item != $myFile)->toArray();

                Storage::delete($deleteFiles);

            } catch (\League\Flysystem\UnableToWriteFile $e) {
                echo 'UnableToWriteFile!' . PHP_EOL . $e->getMessage();
            }
        }
        Mail::to(['gazi.hatas@5deniz.com', 'eser@cdkeyci.com'])->send(new BackupMail(true));
    }
}
