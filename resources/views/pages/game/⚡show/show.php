<?php

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Score;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    private const TOTAL_CARDS = 60;

    public Game $game;

    public array $bids = [];

    public array $actual_wins = [];

    public function mount(string $slug): void
    {
        $this->game = Game::query()
            ->with(['members', 'scores'])
            ->where('manager_id', auth()->id())
            ->where('slug', $slug)
            ->firstOrFail();

        $this->initializeBids();
    }

    private function initializeBids(): void
    {
        foreach ($this->game->members as $member) {
            $this->bids[$member->id] = 0;
        }
    }

    #[Computed]
    public function totalRounds(): int
    {
        $memberCount = $this->game->members->count();

        return $memberCount > 0 ? (int) floor(self::TOTAL_CARDS / $memberCount) : 0;
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
        $this->initializeBids();
        if (! $this->isCurrentRoundComplete()) {
            foreach ($this->game->scores->where('round', $this->latestRound) as $score) {
                $this->bids[$score->member_id] = $score->target_win;
            }
        }
    }

    public function saveBids(): void
    {
        $round = $this->isCurrentRoundComplete() ? ($this->latestRound + 1) : $this->latestRound;

        $this->validate([
            'bids' => 'required|array',
            'bids.*' => "required|integer|min:0|max:{$round}",
        ]);

        $totalBid = collect($this->bids)->sum();
        if ($round === $totalBid) {
            throw ValidationException::withMessages(['bids' => 'Total bids must not be equal to round number.']);
        }

        if ($totalBid < $round - 1) {
            throw ValidationException::withMessages(['bids' => 'Total bids must not be less than round - 1.']);
        }

        DB::transaction(function () use ($round) {
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
            if ($round === $this->totalRounds) {
                Game::query()
                    ->where('id', $this->game->id)
                    ->update([
                        'status' => GameStatus::Finished,
                        'finished_at' => now(),
                    ]);
            }
        });

        $this->game->load('scores');
        Flux::modal('bid-modal')->close();
    }

    public function openEndRoundModal(): void
    {
        $this->actual_wins = [];
        foreach ($this->game->scores->where('round', $this->latestRound) as $score) {
            $this->actual_wins[$score->member_id] = $score->target_win;
        }
    }

    public function saveActualWins(): void
    {
        $this->validate([
            'actual_wins' => 'required|array',
            'actual_wins.*' => 'required|integer|min:0',
        ]);

        $round = $this->latestRound;

        $totalWins = collect($this->actual_wins)->sum();
        if ($round !== $totalWins) {
            throw ValidationException::withMessages(['actual_wins' => 'Total wins must be equal to round number.']);
        }

        // Pre-fetch all scores for this round to avoid N+1 queries
        $scores = $this->game->scores
            ->where('round', $round)
            ->keyBy('member_id');

        DB::transaction(function () use ($round, $scores) {
            foreach ($this->game->members as $member) {
                $score = $scores->get($member->id);
                $target = $score?->target_win ?? 0;
                $actual = Arr::get($this->actual_wins, $member->id, 0);

                $point = $this->calculatePoint($target, $actual);

                Score::query()->updateOrCreate([
                    'game_id' => $this->game->id,
                    'round' => $round,
                    'member_id' => $member->id,
                ], [
                    'actual_win' => $actual,
                    'point' => $point,
                ]);
            }
        });

        $this->game->load('scores');
        Flux::modal('end-round-modal')->close();
    }

    #[Computed]
    public function rounds()
    {
        return $this->game->scores->groupBy('round')->sortKeys();
    }

    #[Computed]
    public function currentResult(): array
    {
        $cumulativePoints = [];
        foreach ($this->game->members as $member) {
            $cumulativePoints[$member->id] = 0;
            foreach ($this->rounds as $scores) {
                $score = $scores->firstWhere('member_id', $member->id);
                if ($score && $score->actual_win !== null) {
                    $cumulativePoints[$member->id] += $score->point;
                }
            }
        }

        return $cumulativePoints;
    }

    private function calculatePoint(int $target, int $actual): int
    {
        if ($target === $actual) {
            return 20 + ($actual * 10);
        }

        return abs($target - $actual) * (-10);
    }
};
