<?php

namespace App\Jobs\Auth;

use App\Enums\RoleEnum;
use App\Mail\TwoFactoryMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTwoFactoryMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $userId;
    protected $twoFactoryCode;

    public function __construct($userId, $twoFactoryCode)
    {
        $this->userId = $userId;
        $this->twoFactoryCode = $twoFactoryCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        Mail::to($user->email)->send(new TwoFactoryMail($this->userId, $this->twoFactoryCode));

    }
}
