<div>
    <form wire:submit="save">
        <x-filament::fieldset>
            <x-slot name="label">
                {{ __('global.settings.dashboard.title') }}
            </x-slot>

            <div class="flex flex-col gap-2">
                <label>
                    <x-filament::input.checkbox wire:model="showLatestClients" />
                    <span>
                        {{ __('global.settings.dashboard.show_last_clients') }}
                    </span>
                </label>
                <label>
                    <x-filament::input.checkbox wire:model="showNextClients" />
                    <span>
                        {{ __('global.settings.dashboard.show_next_clients') }}
                    </span>
                </label>
                <label>
                    <x-filament::input.checkbox wire:model="showSalesFigures" />
                    <span>
                        {{ __('global.settings.dashboard.show_sales_figures') }}
                    </span>
                    <span class="text-sm italic text-slate-500">
                        ({{ __('global.settings.dashboard.widget_visibility') }})
                    </span>
                </label>
                <label>
                    <x-filament::input.checkbox
                                                wire:model="showAppointmentsNumber" />
                    <span>
                        {{ __('global.settings.dashboard.show_appointments_number') }}
                    </span>
                    <span class="text-sm italic text-slate-500">
                        ({{ __('global.settings.dashboard.widget_visibility') }})
                    </span>
                </label>
                <label>
                    <x-filament::input.checkbox wire:model="showMedails" />
                    <span>
                        {{ __('global.settings.dashboard.show_medails') }}
                    </span>
                    <span class="text-sm italic text-slate-500">
                        ({{ __('global.settings.dashboard.widget_visibility') }})
                    </span>
                </label>
                <label>
                    <x-filament::input.checkbox wire:model="showTierlistClients" />
                    <span>
                        {{ __('global.settings.dashboard.show_tierlist_clients') }}
                    </span>
                    <span class="text-sm italic text-slate-500">
                        ({{ __('global.settings.dashboard.widget_visibility') }})
                    </span>
                </label>
                <label>
                    <span>
                        {{ __('global.settings.dashboard.hours_appointments_stay_in_next') }}
                    </span>
                    <x-filament::input.wrapper class="w-20">
                        <x-filament::input type="number"
                                           wire:model="hoursAppointmentsStayInNext" />
                    </x-filament::input.wrapper>
                </label>
            </div>
        </x-filament::fieldset>
        <div class="mt-3 w-full text-right">
            <x-filament::button class="relative px-8"
                                type="submit">
                <x-filament::loading-indicator class="absolute left-[0.5rem] h-5 w-5"
                                               wire:loading />
                {{ __('global.save') }}
            </x-filament::button>
        </div>
    </form>
</div>
