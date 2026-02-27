<?php

use App\Actions\Game\CreateGame;
use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public Collection $games;

    public array $members = [];

    public array $game = [];

    public ?int $playFromGame = null;

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

        $this->playFromGame = null;
        $this->games = $this->getGames();

        Flux::modal('start-game')->close();
        $this->redirectRoute('game.show', ['slug' => $game->slug], navigate: true);
    }

    public function playWithSamePlayers(int $gameId): void
    {
        $game = Game::query()
            ->with(['members'])
            ->where('manager_id', auth()->id())
            ->where('id', $gameId)
            ->firstOrFail();

        $this->playFromGame = $gameId;

        $this->dispatch('init-members', members: $game->members->pluck('name')->toArray(), draggable: true);
        $this->dispatch('open-start-game-modal');
    }

    public function shareGame(int $gameId): void
    {
        $game = Game::query()
            ->where('manager_id', auth()->id())
            ->where('id', $gameId)
            ->firstOrFail();

        if (! $game->sharable_id) {
            $game->update(['sharable_id' => Str::random(8)]);
            $this->games = $this->getGames();
        }

        $this->dispatch('copy-to-clipboard', url: route('game.share', $game->sharable_id));
    }

    public function deleteGame(int $gameId): void
    {
        Game::query()
            ->where('manager_id', auth()->id())
            ->where('id', $gameId)
            ->firstOrFail()
            ->delete();

        $this->games = $this->getGames();
    }

    private function getGames(): Collection
    {
        return Game::query()->where('manager_id', auth()->id())->latest()->get();
    }
};
