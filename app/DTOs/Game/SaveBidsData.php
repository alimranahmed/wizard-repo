<?php

namespace App\DTOs\Game;

use App\Models\Game;

readonly class SaveBidsData
{
    public function __construct(
        public Game $game,
        public int $round,
        public array $bids, // member_id => target_win
    ) {}
}