<?php

namespace App\Console\Commands;

use App\Enums\MarketplaceName;
use App\Enums\OfferStatus;
use App\Models\MarketplaceMatchGame;
use App\Models\User;
use App\Notifications\AutoUpdatePriceNotification;
use App\Services\EnebaService;
use App\Services\GamivoService;
use App\Services\KinguinService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use JetBrains\PhpStorm\ArrayShape;

class AutoUpdateMarketplaceOffer extends Command
{

    protected $signature = 'app:auto-update-marketplace-offer';

    protected $description = 'Pazar yerlerinde Diğer satıcıların tekliflerine gore eşleşmenin fiyatını güncellyen cron';

    const USERS_MAIL = [
        'yigithan.demircin@5deniz.com'
    ];

    protected Collection $users;

    public function __construct()
    {

        parent::__construct();


    }

    public function setUsers(): void
    {
        $this->users = User::whereIn('email', self::USERS_MAIL)->get();
    }

    public function handle(
        EnebaService   $enebaService,
        KinguinService $kinguinService,
        GamivoService  $gamivoService
    )
    {

        $this->setUsers();


//        dd( MarketplaceMatchGame::with('game')->whereMarketplaceId([1,2,3])->whereStatus(1)->first());
        foreach (MarketplaceName::cases() as $case) {

            $matches = $this->marketplaceMatchGame($case->value);

            match ($case->value) {
                MarketplaceName::ENEBA->value => $this->enebaUpdate($matches, $enebaService),
                MarketplaceName::GAMIVO->value => $this->gamivoUpdate($matches, $gamivoService),
                MarketplaceName::KINGUIN->value => $this->kinguinUpdate($matches, $kinguinService),
                default => null
            };
        }

    }

    public function enebaUpdate(Collection $matches, EnebaService $enebaService)
    {
        /** @var MarketplaceMatchGame $match */
        foreach ($matches as $match) {

            $price = $match->amount_us;
            $oldPrice = $match->amount_us;

            $enebaLowPrice = $enebaService->getLowestPrice($match->product_id);
            Log::channel('update_price_cron')->warning('Mevcut fiyatımız : ' . $price . "\n Enebanın en düşük satıcı fiyatı : " . $enebaLowPrice . PHP_EOL);


            if ($price > $enebaLowPrice) {
                $payload = [
                    'offer_id_api' => $match->offer_id,
                    'price' => $enebaLowPrice,
                    'game_id' => $match->game_id,
                    'status' => $match->status,
                ];

                $result = $enebaService->updateOffer($payload);
                if ($result) {

                    $match->update([
                        'amount_us' => $enebaLowPrice
                    ]);


                    $message = $this->logData($match->game_id, $match->game->name, $price, $enebaLowPrice, MarketplaceName::ENEBA, $match->status);

                    $notificationMessage = "{$match->game->name} $price dan  $enebaLowPrice a Eneba'da güncellendi";
                    $status = true;

                    $message = json_encode($message);

                } else {

                    $notificationMessage = "{$match->game->name} bot güncelleme işlemini gerçekleştiremedi!";
                    $status = false;
                    $message = MarketplaceName::ENEBA->name . "'da " . $match->game->name . "oyunu fiyatı guncellenmedi \n";

                }

                Log::channel('update_price_cron')->info($message);
                Notification::send($this->users, new AutoUpdatePriceNotification(MarketplaceName::ENEBA, $notificationMessage, $match->game_id, $status));

            }


        }
    }


    public function kinguinUpdate(Collection $matches, KinguinService $kinguinService)
    {
        /** @var MarketplaceMatchGame $match */
        foreach ($matches as $match) {
            $price = $match->amount_us;

            $kinguinLowPrice = $kinguinService->getLowestPrice($match->product_id);

            if ($price >= $kinguinLowPrice) {

                $payload = [
                    'offer_id_api' => $match->offer_id,
                    'price' => $kinguinLowPrice,
                    'game_id' => $match->game_id,
                    'status' => $match->status,
                ];

                $result = $kinguinService->updateOffer($payload);

                if ($result) {
                    $match->update([
                        'amount_us' => $kinguinLowPrice
                    ]);

                    $message = $this->logData($match->game_id, $match->game->name, $price, $kinguinLowPrice, MarketplaceName::KINGUIN, $match->status);

                    $notificationMessage = "{$match->game->name} $price dan  $kinguinLowPrice a  Kinguin'de güncellendi";
                    $status = true;

                    $message = json_encode($message);

                } else {
                    $notificationMessage = "{$match->game->name} bot güncelleme işlemini gerçekleştiremedi!";
                    $status = false;
                    $message = MarketplaceName::KINGUIN->name . "'da " . $match->game->name . "oyunu fiyatı guncellenmedi \n";
                }

                Log::channel('update_price_cron')->info($message);
                Notification::send($this->users, new AutoUpdatePriceNotification(MarketplaceName::KINGUIN, $notificationMessage, $match->game_id, $status));

            }
        }
    }

    public function gamivoUpdate(Collection $matches, GamivoService $gamivoService)
    {
        /** @var MarketplaceMatchGame $match */
        foreach ($matches as $match) {
            $price = $match->amount_us;

            $gamivoLowPrice = $gamivoService->getLowestPrice($match->product_id);

            if ($price > $gamivoLowPrice) {

                $payload = [
                    'offer_id_api' => $match->offer_id,
                    'price' => $gamivoLowPrice,
                    'game_id' => $match->game_id,
                    'status' => $match->status,
                ];

                $result = $gamivoService->updateOffer($payload);

                if ($result) {
                    $match->update([
                        'amount_us' => $gamivoLowPrice
                    ]);

                    $message = $this->logData($match->game_id, $match->game->name, $price, $gamivoLowPrice, MarketplaceName::GAMIVO, $match->status);

                    $notificationMessage = "{$match->game->name} $price dan  $gamivoLowPrice a Gamivo'da güncellendi";
                    $status = true;

                    $message = json_encode($message);

                } else {
                    $notificationMessage = "{$match->game->name} bot güncelleme işlemini gerçekleştiremedi!";
                    $status = false;
                    $message = MarketplaceName::GAMIVO->name . "'da " . $match->game->name . "oyunu fiyatı guncellenmedi \n";

                }
                Log::channel('update_price_cron')->info($message);
                Notification::send($this->users, new AutoUpdatePriceNotification(MarketplaceName::GAMIVO, $notificationMessage, $match->game_id, $status));

            }
        }
    }


    public function marketplaceMatchGame(int|string $marketplaceId): Collection
    {
        return MarketplaceMatchGame::with('game')->whereMarketplaceId($marketplaceId)->whereId(2166)->get();


    }

    #[ArrayShape(["GameName" => "", "GameId" => "", "OldPrice" => "", "NewPrice" => "", "MarketplaceId" => "\App\Enums\MarketplaceName", "Status" => "string"])] public function logData($gameId, $gameName, $oldPrice, $newPrice, MarketplaceName $marketplaceName, $status): array
    {
        return [
            "GameName" => $gameName,
            "GameId" => $gameId,
            "OldPrice" => $oldPrice,
            "NewPrice" => $newPrice,
            "MarketplaceId" => $marketplaceName,
            "Status" => OfferStatus::from($status)->name
        ];

    }
}
