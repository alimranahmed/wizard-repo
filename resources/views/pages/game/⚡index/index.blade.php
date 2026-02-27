<div
    x-data="{ copied: false }"
    x-on:copy-to-clipboard.window="navigator.clipboard.writeText($event.detail.url); copied = true; setTimeout(() => copied = false, 2500)"
    x-on:open-start-game-modal.window="$flux.modal('start-game').show()"
>
    <div
        x-show="copied"
        x-transition
        class="fixed bottom-4 right-4 z-50 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-sm px-4 py-2 rounded shadow-lg"
    >
        Link copied to clipboard!
    </div>

    <div class="flex justify-between items-center mb-3">
        <div class="text-2xl font-bold">Wizard</div>
        <div @click="$dispatch('init-members', { members: ['', ''], draggable: false })">
            <flux:modal.trigger name="start-game">
                <flux:button>Start Game</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    <div class="flex flex-col gap-4">
        @forelse($games as $game)
        <div class="border rounded p-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:link href="{{ route('game.show', $game->slug) }}" wire:navigate>{{ $game->name }}</flux:link>
                    <div class="text-zinc-400 text-sm my-1">
                        {{$game->started_at->format('jS M Y h:i A')}} - {{$game->finished_at?->toTimeString() ?: 'in progress'}}
                    </div>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ $game->members->pluck('name')->implode(', ') }}
                    </div>
                </div>
                <flux:dropdown>
                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" />
                    <flux:menu>
                        <flux:menu.item icon="play" wire:click="playWithSamePlayers({{ $game->id }})">
                            Play with same players
                        </flux:menu.item>
                        <flux:menu.item icon="share" wire:click="shareGame({{ $game->id }})">
                            Share
                        </flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item icon="trash" variant="danger" wire:click="deleteGame({{ $game->id }})" wire:confirm="Delete this game?">
                            Delete
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>
        @empty
            <div class="text-shadow-slate-400">No game played yet</div>
        @endforelse
    </div>

    <flux:modal name="start-game" flyout>
        <div
            class="space-y-6"
            x-data="{
                members: ['', ''],
                draggable: false,
                dragIdx: null,
                reorder(targetIdx) {
                    if (this.dragIdx === null || this.dragIdx === targetIdx) return;
                    let item = this.members.splice(this.dragIdx, 1)[0];
                    this.members.splice(targetIdx, 0, item);
                    this.dragIdx = null;
                }
            }"
            @init-members.window="members = $event.detail.members; draggable = $event.detail.draggable ?? false"
        >

            <flux:heading size="lg">Start a Game</flux:heading>

            @if ($errors->any())
                <div class="text-red-500 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Members</div>

            <div class="space-y-2">
                <template x-for="(member, index) in members" :key="index">
                    <div
                        class="flex items-center gap-2"
                        :draggable="draggable"
                        @dragstart="draggable && (dragIdx = index)"
                        @dragover.prevent
                        @drop="draggable && reorder(index)"
                        :class="draggable ? 'cursor-grab' : ''"
                    >
                        <flux:icon
                            x-show="draggable"
                            name="bars-3"
                            class="shrink-0 text-zinc-400 cursor-grab"
                        />
                        <flux:input
                            x-bind:placeholder="`Member ${index + 1}: Name`"
                            x-model="members[index]"
                            class="flex-1"
                        />
                    </div>
                </template>
            </div>

            <template x-if="members.length < 20">
                <button type="button"
                        class="rounded-full p-1 hover:bg-slate-100 border border-slate-300"
                        @click="members.push('')">
                    <flux:icon name="plus" />
                </button>
            </template>

            <div class="flex">
                <flux:spacer />
                <flux:button type="button" variant="primary" @click="$wire.saveMembers(members);">
                    Save changes
                </flux:button>
            </div>

        </div>
    </flux:modal>

</div>
