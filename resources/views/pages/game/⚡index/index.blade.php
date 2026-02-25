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
                <div><flux:link href="{{route('game.show', $game->slug)}}">{{$game->name}}</flux:link></div>
                <div>{{$game->members->pluck('name')->implode(', ')}}</div>
            </div>
        @empty
            <div class="text-shadow-slate-400">No game played yet</div>
        @endforelse
    </div>

    <flux:modal name="start-game" flyout>
        <div
            class="space-y-6"
            x-data="{ members: [''] }"
        >

            <flux:heading size="lg">Start a Game</flux:heading>

            @if ($errors->any())
                <div class="text-red-500 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <template x-for="(member, index) in members" :key="index">
                <flux:input
                    label="Member"
                    placeholder="Name"
                    x-model="members[index]"
                />
            </template>

            <flux:button
                type="button"
                @click="members.push('')"
            >
                Add More
            </flux:button>

            <div class="flex">
                <flux:spacer />
                <flux:button
                    type="button"
                    variant="primary"
                    @click="$wire.saveMembers(members);"
                >
                    Save changes
                </flux:button>
            </div>

        </div>
    </flux:modal>
</div>
