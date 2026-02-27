<?php

namespace App\Actions\Game;

use App\DTOs\Game\SaveActualWinsData;
use App\Models\Score;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SaveActualWins
{
    public function handle(SaveActualWinsData $data): void
    {
        $scores = $data->game->scores
            ->where('round', $data->round)
            ->keyBy('member_id');

        DB::transaction(function () use ($data, $scores) {
            foreach ($data->game->members as $member) {
                $score = $scores->get($member->id);
                $target = $score?->target_win ?? 0;
                $actual = Arr::get($data->actualWins, $member->id, 0);

                Score::query()->updateOrCreate([
                    'game_id' => $data->game->id,
                    'round' => $data->round,
                    'member_id' => $member->id,
                ], [
                    'actual_win' => $actual,
                    'point' => $this->calculatePoint($target, $actual),
                ]);
            }
        });
    }

    private function calculatePoint(int $target, int $actual): int
    {
        if ($target === $actual) {
            return 20 + ($actual * 10);
        }

        return abs($target - $actual) * (-10);
    }
}