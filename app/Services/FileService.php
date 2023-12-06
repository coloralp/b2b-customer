<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class FileService
{

    public function deleteFiles($chanel): void
    {
        $logs = Storage::disk($chanel);
        foreach ($logs as $log) {
            $timeStamp = Storage::disk($chanel)->lastModified($log);
            $date = date("d.m.Y", $timeStamp);
            $dayDiff = now()->diff(Carbon::parse($date))->d;

            if ($dayDiff >= 20) {
                Storage::disk($chanel)->delete($log);
            }
        }
    }

}
