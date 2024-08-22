<x-filament-panels::page x-data="{ activeTab: 'appointments' }">
    <x-filament::tabs>

        <x-filament::tabs.item alpine-active="activeTab === 'appointments'"
            x-on:click="activeTab = 'appointments'">
            {{ __('client.view.appointments') }}
        </x-filament::tabs.item>

        <x-filament::tabs.item alpine-active="activeTab === 'coordinates'"
            x-on:click="activeTab = 'coordinates'; $dispatch('active-tab-changed')">
            {{ __('client.view.coordinates') }}
        </x-filament::tabs.item>

    </x-filament::tabs>

    <div x-show="activeTab === 'appointments'">
        @if (count($relationManagers = $this->getRelationManagers()))
            <x-filament-panels::resources.relation-managers :active-manager="$this->activeRelationManager"
                :managers="$relationManagers" :owner-record="$record" :page-class="static::class" />
        @endif
    </div>
    <div class="flex-none self-start" x-show="activeTab === 'coordinates'">
        @if ($this->hasInfolist())
            {{ $this->infolist }}
        @else
            {{ $this->form }}
        @endif
    </div>
</x-filament-panels::page>
