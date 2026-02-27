<?php

use App\Actions\Game\CreateGame;
use App\Models\Game;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Collection $games;

    public array $members = [];

    public array $game = [];

    public function mount(): void
    {
        $this->games = $this->getGames();
    }

    public array $rules = [
        'members' => 'array|min:2|max:10',
        'members.*' => 'required|string|min:3|max:50',
    ];

    public function saveMembers(array $members): void
    {
        $this->members = $members;

        $this->validate();

        $game = (new CreateGame)->handle(auth()->id(), $members, $this->game);

        $this->games = $this->getGames();

        Flux::modal('start-game')->close();
        $this->redirectRoute('game.show', ['slug' => $game->slug], navigate: true);
    }

    private function getGames(): Collection
    {
        return Game::query()->where('manager_id', auth()->id())->latest()->get();
    }
};
