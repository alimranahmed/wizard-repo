<?php

use App\Models\Game;
use App\Models\Member;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Collection $games;

    public array $members = [];

    public function mount(): void
    {
        $this->games = $this->getGames();
    }

    public array $rules = [
        'members' => 'array|min:2|max:10',
        'members.*' => 'required|string|min:3|max:255',
    ];

    public function saveMembers(array $members): void
    {
        $this->members = $members;

        $this->validate();

        $totalGame = Game::query()->whereToday('created_at')->count();

        $game = Game::query()
            ->create([
                'name' => today()->format('jS M Y').'(Game '.($totalGame + 1).')']
            );

        foreach ($members as $member) {
            Member::create(['name' => $member, 'game_id' => $game->id]);
        }

        $this->games = $this->getGames();

        Flux::modal('start-game')->close();
    }

    private function getGames(): Collection
    {
        return Game::query()->latest()->get();
    }
};
