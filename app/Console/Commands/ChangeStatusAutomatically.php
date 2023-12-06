<?php

namespace App\Console\Commands;

use App\Jobs\Marketplace1\ChangeOfferStatusJob;
use App\Models\MarketplaceMatchGame;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ChangeStatusAutomatically extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:change-status-automatically {mId} {status} {kim}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userName = explode(" ", $this->argument('kim'))[0] ?? 'bulmadı';
        $userLastname = explode(" ", $this->argument('kim'))[1] ?? 'bulmadı';

        $status = (int)$this->argument('status');


        if (!in_array($status, [1, 0])) {
            $this->error('0 1 dışında status yollayamazsınız');
            return 0;
        }

        $user = User::whereFirstName($userName)->whereLastName($userLastname)->first();

        if (!$user) {
            $this->error('Bu lullanıcı bulunamdı');
            return 0;
        }


        MarketplaceMatchGame::whereMarketplaceId($this->argument('mId'))
            ->chunk(50, function (Collection $collection) use ($user, $status) {
                dispatch(new ChangeOfferStatusJob($collection->pluck('id')->toArray(), $status, $user->id));
            });
    }
}
