<x-filament-panels::page x-data="{ activeTab: 'display' }">
    <x-filament::tabs>
        <x-filament::tabs.item
            alpine-active="activeTab === 'display'"
            x-on:click="activeTab = 'display'"
        >
            {{ __('global.settings.display') }}
        </x-filament::tabs.item>

        <x-filament::tabs.item
            alpine-active="activeTab === 'team'"
            x-on:click="activeTab = 'team'"
        >
            {{ __('global.settings.team') }}
        </x-filament::tabs.item>
    </x-filament::tabs>
    <x-filament::section x-show="activeTab == 'display'">
        <livewire:display-settings />
    </x-filament::section>
    <x-filament::section x-show="activeTab == 'team'">
        <div class="grid grid-cols-[3fr_2fr] gap-3">
            <div>
                <livewire:team-settings />
            </div>
            <div>
                <livewire:team-select />
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
