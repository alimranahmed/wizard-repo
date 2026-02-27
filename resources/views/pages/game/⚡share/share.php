<?php

use App\Models\Game;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.public')] class extends Component
{
    private const TOTAL_CARDS = 60;

    public Game $game;

    public function mount(string $sharable_id): void
    {
        $this->game = Game::query()
            ->with(['members', 'scores'])
            ->where('sharable_id', $sharable_id)
            ->firstOrFail();
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
};
