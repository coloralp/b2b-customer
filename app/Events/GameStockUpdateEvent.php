<?php

namespace App\Events;

use App\Models\Game;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStockUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct(public Game $game)
    {
        //
    }
}
