<div>
    <form wire:submit="addTeamMember">
        <div class="flex gap-3">
            <x-filament::input.wrapper :valid="! $errors->has('newTeamMember')" class="w-full">
                <x-slot name="prefix">
                    @
                </x-slot>
                <x-filament::input
                    type="text"
                    wire:model="newTeamMember"
                    placeholder="{{ __('global.settings.team.add.placeholder') }}"
                />
            </x-filament::input.wrapper>
            <x-filament::button type="submit" class="px-8 relative">
                <x-filament::loading-indicator wire:loading class="h-5 w-5 absolute left-[0.5rem] " />
                {{ __('global.add') }}
            </x-filament::button>
        </div>
        @error('newTeamMember')
            <div class="text-rose-600 font-sm">{{ $message }}</div>
        @enderror
    </form>
</div>
