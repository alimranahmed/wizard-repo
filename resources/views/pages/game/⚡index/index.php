<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Member;
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

        $totalGame = Game::query()
            ->whereToday('created_at')
            ->where('manager_id', auth()->id())
            ->count();

        $game = Game::query()
            ->create([
                'manager_id' => auth()->id(),
                'password' => Hash::make(Arr::get($this->game, 'password', 'secret')),
                'name' => $name = today()->format('jS M Y').'(Game '.($totalGame + 1).')',
                'slug' => Arr::get($this->game, 'slug') ?: Str::slug($name),
                'status' => GameStatus::Progress,
                'started_at' => now(),
            ]);

        $members = collect($members)->map(function (string $member) {
            $memberName = trim($member);

            return Member::query()->updateOrCreate([
                'name' => $memberName,
                'added_by' => auth()->id(),
            ]);
        });

        $game->members()->attach($members->pluck('id'));

        $this->games = $this->getGames();

        Flux::modal('start-game')->close();
        $this->redirectRoute('game.show', ['slug' => $game->slug], navigate: true);
    }

    private function getGames(): Collection
    {
        return Game::query()->where('manager_id', auth()->id())->latest()->get();
    }
};
