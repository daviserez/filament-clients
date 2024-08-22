<div>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('global.settings.team.select') }}
        </x-slot>

        <x-slot name="description">
            {{ __('global.settings.team.change.help') }}
        </x-slot>

        <form wire:submit="changeTeam">
            <div class="flex gap-3">
                <x-filament::input.wrapper class="w-full">
                    <x-filament::input.select wire:model="selectedTeamId">
                        <option value="{{ auth()->user()->id }}" {{auth()->user()->id === auth()->user()->team_id ? "selected" : ""}}>{{ __('global.settings.team.self') }}</option>
                        @foreach (auth()->user()->getTeamsAsMember() as $teamId => $team)
                            <option value="{{ $teamId }}" {{auth()->user()->team_id === $teamId ? "selected" : ""}}>{{ $team }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::button type="submit" class="px-8 relative">
                    <x-filament::loading-indicator wire:loading class="h-5 w-5 absolute left-[0.5rem] " />
                    {{ __('global.edit') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>
</div>
