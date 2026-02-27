<?php

namespace App\Actions\Game;

use App\DTOs\Game\SaveBidsData;
use App\Enums\GameStatus;
use App\Models\Score;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SaveBids
{
    private const TOTAL_CARDS = 60;

    public function handle(SaveBidsData $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data->game->members as $member) {
                Score::query()->updateOrCreate([
                    'game_id' => $data->game->id,
                    'round' => $data->round,
                    'member_id' => $member->id,
                ], [
                    'target_win' => Arr::get($data->bids, $member->id, 0),
                    'actual_win' => null,
                    'point' => 0,
                ]);
            }

            $totalRounds = (int) floor(self::TOTAL_CARDS / $data->game->members->count());
            if ($data->round === $totalRounds) {
                $data->game->update([
                    'status' => GameStatus::Finished,
                    'finished_at' => now(),
                ]);
            }
        });
    }
}