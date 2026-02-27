<div>
    <div class="flex justify-between items-center mb-6">
        <flux:heading>{{ $game->name }}</flux:heading>

        <div class="flex gap-2">
            @if($this->isCurrentRoundComplete)
                <flux:modal.trigger name="bid-modal">
                    <flux:button wire:click="openBidModal()">Bid (Round {{ $this->latestRound + 1 }})</flux:button>
                </flux:modal.trigger>
            @else
                <flux:modal.trigger name="bid-modal">
                    <flux:button variant="subtle" wire:click="openBidModal()">Edit Bids</flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="end-round-modal">
                    <flux:button variant="primary" wire:click="openEndRoundModal()">End Round {{ $this->latestRound }}
                    </flux:button>
                </flux:modal.trigger>
            @endif
        </div>
    </div>

    <!-- The Scoreboard Table -->
    <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 rounded-lg">
        <table class="w-full text-sm text-left">
            <thead class="bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="w-0.5"></th>
                    @foreach($game->members as $member)
                        <th class="px-3 py-2 font-semibold text-zinc-900 dark:text-zinc-100 text-center border-l border-zinc-200 dark:border-zinc-700 flex-1 w-48 align-top">
                            <div>
                                {{ $member->initials() }}
                                <div class="md:inline text-xs md:text-sm text-zinc-900 dark:text-zinc-100">
                                    ({{$this->currentResult[$member->id] }})
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
                    <tr
                        class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $this->latestRound == $roundNumber ? 'bg-zinc-50 dark:bg-zinc-800/80' : '' }}">
                        <td
                            class="px-1 md:px-3 py-2 text-center font-medium {{ $scores->isEmpty() ? 'text-zinc-400 dark:text-zinc-500' : 'text-zinc-900 dark:text-zinc-100' }}">
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
                                        <div
                                            class="md:inline font-bold {{ $score->point > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $score->point > 0 ? '+' : '' }}{{ $score->point }}
                                        </div>
                                        <div class="md:inline text-zinc-500 dark:text-zinc-400 font-medium whitespace-nowrap">
                                            ({{ $score->actual_win }}/{{ $score->target_win }})
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center">
                                        <span
                                            class="text-zinc-500 bg-zinc-100 dark:bg-zinc-800 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700 px-0.5 py-0.5 rounded text-xs font-medium"
                                            title="Bid">Bid: {{ $score->target_win }}</span>
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
                                    ({{$this->currentResult[$member->id] }})
                                </div>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>

    <!-- Modals -->
    <flux:modal name="bid-modal" flyout>
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $this->isCurrentRoundComplete ? "Bidding(Round: ".($this->latestRound + 1).")" : "Edit Bids(Round: {$this->latestRound})" }}
            </flux:heading>

            <form wire:submit.prevent="saveBids()" class="space-y-4">

                @foreach($game->members as $member)
                    <div class="flex gap-2 justify-between items-center">
                        <flux:input type="number"
                                    label="{{ $member->name }}'s Bid"
                                    min="0"
                                    wire:model="bids.{{ $member->id }}"
                                    required />
                    </div>
                @endforeach

                <div class="flex justify-end pt-4">
                    <flux:button type="submit" variant="primary">Save Bids</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal name="end-round-modal" flyout>
        <div class="space-y-6">
            <flux:heading size="lg">Results(Round: {{ $this->latestRound }})</flux:heading>
            <flux:subheading>Enter the actual wins for each player.</flux:subheading>

            <form wire:submit.prevent="saveActualWins()" class="space-y-4">

                @foreach($game->members as $member)
                    @php
                        $target = $game->scores
                            ->where('round', $this->latestRound)
                            ->where('member_id', $member->id)
                            ->first()?->target_win ?? 0;
                    @endphp
                    <flux:input type="number" min="0" label="{{ $member->name }} won(Bid: {{ $target }})"
                        wire:model="actual_wins.{{ $member->id }}" required />
                @endforeach

                <div class="flex justify-end pt-4">
                    <flux:button type="submit" variant="primary">Save & End Round</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
