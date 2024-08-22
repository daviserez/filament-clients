@use('App\Helpers\Utils')
@use('App\Models\Service')

@php
    $appointment = $getRecord();
    $totalPrice = $appointment->details()->sum('price');
    $totalServicePrice = $appointment->details()->sum('service_price');
@endphp

<div
     class="flex w-full flex-col items-start justify-start gap-3 px-3 pb-2 text-sm sm:flex-row lg:px-20">
    <div class="flex flex-col gap-1">
        <div class="min-w-[10rem] pt-2 text-left font-bold sm:text-center">
            {{ Utils::formatDate($appointment->appointed_at) }}
        </div>
        <div class="min-w-[10rem] text-left sm:text-center">
            {{ Utils::formatHours($appointment->appointed_at) }}
        </div>
    </div>
    <div class="w-full grow">
        @foreach ($appointment->details as $details)
            <div
                 class="items-top flex border-b border-slate-200 pt-2 dark:border-slate-700">
                <div class="flex grow flex-wrap items-baseline gap-1">
                    <div class="flex items-center gap-2">
                        @if (is_null($details->service))
                            <x-filament::icon class="h-3 w-3 text-gray-500 dark:text-gray-400"
                                              icon="heroicon-s-archive-box" />
                        @endif
                        {{ $details->service()->withTrashed()->first()->name }}
                    </div>
                    <div class="pl-2 italic text-slate-400">
                        {{ $details->comment }}
                    </div>
                </div>
                @if ($details->service_price !== $details->price)
                    <div class="w-24 text-right text-red-500 line-through">
                        {{ $details->service_price }} {{ config('app.currency') }}
                    </div>
                @endif
                <div class="w-24 text-right">
                    {{ $details->price }} {{ config('app.currency') }}
                </div>
            </div>
        @endforeach
        <div class="items-top mt-2 flex rounded-sm font-bold">
            <div class="flex grow flex-wrap items-baseline gap-2">
                <div>
                    {{ __('client.appointment.details.total') }}
                </div>
            </div>
            @if ($totalPrice !== $totalServicePrice)
                <div class="w-24 text-right text-red-500 line-through">
                    {{ Utils::formatNumber($totalServicePrice) }}
                    {{ config('app.currency') }}
                </div>
            @endif
            <div class="w-24 text-right">
                {{ Utils::formatNumber($totalPrice) }}
                {{ config('app.currency') }}
            </div>
        </div>
    </div>
</div>
