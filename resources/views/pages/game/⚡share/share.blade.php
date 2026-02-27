<div>
    <div class="flex justify-between items-center mb-6">
        <flux:heading>{{ $game->name }}</flux:heading>
        <flux:badge color="zinc">View Only</flux:badge>
    </div>
    
    <div class="text-zinc-400 text-sm mb-3">
        {{$game->started_at->format('jS M Y h:i A')}} - {{$game->finished_at?->format('h:i A') ?: 'in progress'}}
    </div>
    <!-- Scoreboard -->
    <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg" wire:poll.visible.7s>
        <table class="w-full text-sm text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="w-0.5"></th>
                    @foreach($game->members as $member)
                        <th class="px-3 py-2 font-semibold text-zinc-900 dark:text-zinc-100 text-center border-l border-zinc-200 dark:border-zinc-700 flex-1 w-48 align-top">
                            <div>
                                {{ $member->initials() }}
                                <div class="md:inline text-xs md:text-sm text-zinc-900 dark:text-zinc-100">
                                    ({{ $this->currentResult[$member->id] }})
                                </div>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 text-xs md:text-sm">
                @for($roundNumber = 1; $roundNumber <= $this->totalRounds; $roundNumber++)
                    @php
                        $scores = $this->rounds->get($roundNumber, collect());
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $this->latestRound == $roundNumber ? 'bg-zinc-50 dark:bg-zinc-800/80' : '' }}">
                        <td class="px-1 md:px-3 py-2 text-center font-medium {{ $scores->isEmpty() ? 'text-zinc-400 dark:text-zinc-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                            {{ $roundNumber }}
                        </td>
                        @foreach($game->members as $member)
                            @php
                                $score = $scores->firstWhere('member_id', $member->id);
                            @endphp
                            <td class="px-0.5 py-2 text-center border-l border-zinc-200 dark:border-zinc-700">
                                @if($score)
                                    @if($score->actual_win !== null)
                                        <div class="justify-center gap-1.5">
                                            <div class="md:inline font-bold {{ $score->point > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $score->point > 0 ? '+' : '' }}{{ $score->point }}
                                            </div>
                                            <div class="md:inline text-zinc-500 dark:text-zinc-400 font-medium whitespace-nowrap">
                                                ({{ $score->actual_win }}/{{ $score->target_win }})
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center">
                                            <span class="text-zinc-500 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 px-0.5 py-0.5 rounded text-xs font-medium">
                                                Bid: {{ $score->target_win }}
                                            </span>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-zinc-200 dark:text-zinc-700">-</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="w-0.5"></th>
                    @foreach($game->members as $member)
                        <th class="px-3 py-2 font-semibold text-zinc-900 dark:text-zinc-100 text-center border-l border-zinc-200 dark:border-zinc-700 flex-1 w-48 align-top">
                            <div>
                                {{ $member->initials() }}
                                <div class="md:inline text-xs md:text-sm text-zinc-900 dark:text-zinc-100">
                                    ({{ $this->currentResult[$member->id] }})
                                </div>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>

    @if($this->latestRound === $this->totalRounds && $this->isCurrentRoundComplete)
        <x-game.leaderboard :members="$game->members" :currentResult="$this->currentResult" />
    @endif
</div>
