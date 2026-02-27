<?php

namespace App\DTOs\Game;

use App\Models\Game;

readonly class SaveActualWinsData
{
    public function __construct(
        public Game $game,
        public int $round,
        public array $actualWins, // member_id => actual_win
    ) {}
}