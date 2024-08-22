<div>
    <x-filament::section>
        <x-slot name="heading">
            {{ auth()->user()->getTeamName() }}
        </x-slot>

        <x-slot name="description">
            {{ __('global.settings.team.help') }}<br>
            {{ __('global.settings.team.help_1') }}
        </x-slot>
        <x-filament::fieldset>
            <x-slot name="label">
                {{ __('global.settings.team.members') }}
            </x-slot>

            <livewire:team-settings-add-member @added="$refresh" />
            <ul>
                @foreach ($this->teamMembers as $teamMember)
                    <livewire:team-settings-delete-member :$teamMember :key="$teamMember->id" @deleted="$refresh" />
                @endforeach
            </ul>
        </x-filament::fieldset>
    </x-filament::section>
</div>
