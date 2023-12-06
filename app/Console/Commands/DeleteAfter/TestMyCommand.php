<?php

namespace App\Console\Commands\DeleteAfter;


use App\Enums\MarketplaceName;
use App\Enums\RoleEnum;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestMyCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * Execute the console command.
     */
    public function handle()
    {

//        Game::withCount('activeKeys')
//            ->orderByDesc('created_at')
//            ->chunk(10, function (Collection $collection) {
//
//                /** @var Game $game */
//                foreach ($collection as $game) {
//                    $game->update(['stock' => $game->active_keys_count]);
//                }
//            });

//        Order::withCount('keys')->orderByDesc('created_at')
//            ->chunk(1000, function (Collection $collection) {
//                /** @var Order $item */
//                foreach ($collection as $item) {
//                    $item->update(['piece' => $item->keys_count]);
//                }
//            });


//        foreach (MarketplaceName::cases() as $case) {
//            $user = User::create([
//                'name' => $case->defineCustomerName(),
//                'first_name' => $case->defineCustomerName(),
//                'last_name' => $case->defineCustomerName(),
//                'surname' => $case->defineCustomerName(),
//                'email' => $case->getEmail(),
//                'password' => \Hash::make($case->getEmail())
//            ]);
//
//            $user->assignRole(RoleEnum::CUSTOMER);
//
//        }

    }
}
