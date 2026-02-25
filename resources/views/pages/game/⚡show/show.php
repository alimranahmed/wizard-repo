<?php

use App\Models\Game;
use App\Models\Score;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Game $game;

    public array $bids = [];

    public array $actuals = [];

    public function mount(string $slug): void
    {
        $this->game = Game::query()
            ->with(['members', 'scores'])
            ->where('manager_id', auth()->id())
            ->where('slug', $slug)
            ->firstOrFail();
    }

    #[Computed]
    public function totalRounds(): int
    {
        $memberCount = $this->game->members->count();

        return $memberCount > 0 ? floor(60 / $memberCount) : 0;
    }

    #[Computed]
    public function latestRound(): int
    {
        return $this->game->scores->max('round') ?? 0;
    }

    #[Computed]
    public function isCurrentRoundComplete(): bool
    {
        if ($this->latestRound === 0) {
            return true;
        }

        return $this->game->scores
            ->where('round', $this->latestRound)
            ->whereNull('actual_win')->isEmpty();
    }

    public function openBidModal(): void
    {
        $this->bids = [];
        if (! $this->isCurrentRoundComplete) {
            foreach ($this->game->scores->where('round', $this->latestRound) as $score) {
                $this->bids[$score->member_id] = $score->target_win;
            }
        }
    }

    public function saveBids(): void
    {
        $this->validate([
            'bids' => 'required|array',
            'bids.*' => 'required|integer|min:0',
        ]);

        $round = $this->isCurrentRoundComplete ? ($this->latestRound + 1) : $this->latestRound;

        $totalBid = collect($this->bids)->sum();
        if ($round == $totalBid) {
            throw ValidationException::withMessages(['bids' => 'Total bids must not be equal to round number.']);
        }

        if ($totalBid < $round - 1) {
            throw ValidationException::withMessages(['bids' => 'Total bids must not be less than round - 1.']);
        }

        foreach ($this->game->members as $member) {
            Score::query()->updateOrCreate([
                'game_id' => $this->game->id,
                'round' => $round,
                'member_id' => $member->id,
            ], [
                'target_win' => Arr::get($this->bids, $member->id, 0),
                'actual_win' => null,
                'point' => 0,
            ]);
        }

        // reload relationships
        $this->game->load('scores');
    }

    public function openEndRoundModal(): void
    {
        $this->actuals = [];
        foreach ($this->game->scores->where('round', $this->latestRound) as $score) {
            $this->actuals[$score->member_id] = '';
        }
    }

    public function saveActuals(): void
    {
        $this->validate([
            'actuals' => 'required|array',
            'actuals.*' => 'required|integer|min:0',
        ]);

        $round = $this->latestRound;

        $totalWins = collect($this->actuals)->sum();
        if ($round != $totalWins) {
            throw ValidationException::withMessages(['actuals' => 'Total wins must be equal to round number.']);
        }

        foreach ($this->game->members as $member) {
            $target = $this->game->scores
                ->where('round', $round)
                ->where('member_id', $member->id)
                ->first()->target_win ?? 0;

            $actual = Arr::get($this->actuals, $member->id, 0);

            if ($target == $actual) {
                $point = 20 + ($actual * 10);
            } else {
                $point = abs($target - $actual) * (-10);
            }

            Score::query()->updateOrCreate([
                'game_id' => $this->game->id,
                'round' => $round,
                'member_id' => $member->id,
            ], [
                'actual_win' => $actual,
                'point' => $point,
            ]);
        }

        $this->game->load('scores');
    }
};
