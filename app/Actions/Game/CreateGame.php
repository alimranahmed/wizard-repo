<?php

namespace App\Actions\Game;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateGame
{
    public function handle(int $managerId, array $members, array $gameData = []): Game
    {
        $totalGame = Game::query()
            ->whereToday('created_at')
            ->where('manager_id', $managerId)
            ->count();

        $game = Game::query()->create([
            'manager_id' => $managerId,
            'password' => Hash::make(Arr::get($gameData, 'password', 'secret')),
            'name' => today()->format('jS M Y').': Game '.($totalGame + 1),
            'slug' => Str::slug(today()->format('Y-m-d').'-game-'.$totalGame + 1),
            'status' => GameStatus::Progress,
            'started_at' => now(),
        ]);

        $members = collect($members)->map(function (string $member) use ($managerId) {
            return Member::query()->updateOrCreate([
                'name' => trim($member),
                'added_by' => $managerId,
            ]);
        });

        $game->members()->attach(
            $members->values()->mapWithKeys(fn ($member, $index) => [
                $member->id => ['order' => $index + 1],
            ])
        );

        return $game;
    }
}
