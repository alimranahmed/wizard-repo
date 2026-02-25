<div>
    <div class="flex justify-between items-center mb-3">
        <div class="text-2xl font-bold">Wizard</div>
        <flux:modal.trigger name="start-game">
            <flux:button>Start Game</flux:button>
        </flux:modal.trigger>
    </div>

    <div class="flex flex-col gap-4">
        @forelse($games as $game)
        <div class="border rounded p-4">
            <div>
                <flux:link href="{{route('game.show', $game->slug)}}">{{$game->name}}</flux:link>
            </div>
            <div>{{$game->members->pluck('name')->implode(', ')}}</div>
        </div>
        @empty
            <div class="text-shadow-slate-400">No game played yet</div>
        @endforelse
    </div>

    <flux:modal name="start-game" flyout>
        <div class="space-y-6" x-data="{ members: ['', ''] }">

            <flux:heading size="lg">Start a Game</flux:heading>

            @if ($errors->any())
                <div class="text-red-500 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div>Members</div>
            <template x-for="(member, index) in members" :key="index">
                <flux:input x-bind:placeholder="`Member ${index + 1}: Name`" x-model="members[index]" />
            </template>

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
