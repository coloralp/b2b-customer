<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateStockCurrent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-stock-current {--gameId=}';

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

        Game::orderByDesc('created_at')
            ->when(!is_null($this->option('gameId')), function (Builder $query) {
                $query->where('id', $this->option('gameId'));
            })
            ->chunk(10, function (Collection $collection) {
                /** @var Game $item */
                foreach ($collection as $item) {
                    $item->update(['stock' => GameService::getOnlyStock($item->id)]);
                }
            });
    }
}
