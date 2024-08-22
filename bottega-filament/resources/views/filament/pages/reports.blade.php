@use('App\Filament\Pages\ReportsType')

<x-filament-panels::page x-data="{ activeTab: 'dates', showReductions: false }">
        <div class="flex gap-3 items-center m-auto">
            <div>
                <x-filament::tabs>
                    <x-filament::tabs.item
                        alpine-active="activeTab === 'dates'"
                        x-on:click="activeTab = 'dates'"
                    >
                        {{ __('global.reports.dates') }}
                    </x-filament::tabs.item>

                    <x-filament::tabs.item
                        alpine-active="activeTab === 'clients'"
                        x-on:click="activeTab = 'clients'"
                    >
                        {{ __('global.reports.clients') }}
                    </x-filament::tabs.item>

                    <x-filament::tabs.item
                        alpine-active="activeTab === 'services'"
                        x-on:click="activeTab = 'services'"
                    >
                        {{ __('global.reports.services') }}
                    </x-filament::tabs.item>
                </x-filament::tabs>
            </div>

            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="year">
                    @foreach(range(date('Y'), date('Y') - 10) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>

            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="month">
                    <option value="0">{{__('global.reports.month.all')}}</option>
                    @foreach(range(1, 12) as $month)
                        <option value="{{ $month }}">{{ \Carbon\Carbon::create(month: $month)->monthName }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        <x-filament::section x-show="activeTab == 'dates'" class="m-auto">
            <x-report-date :data="$this->getData(ReportsType::Date)" />
        </x-filament::section>

        <x-filament::section x-show="activeTab == 'clients'" class="m-auto">
            <x-report-client :data="$this->getData(ReportsType::Client)" />
        </x-filament::section>

        <x-filament::section x-show="activeTab == 'services'" class="m-auto">
            <x-report-service :data="$this->getData(ReportsType::Service)" />
        </x-filament::section>
</x-filament-panels::page>
