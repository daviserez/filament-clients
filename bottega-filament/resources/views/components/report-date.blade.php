@use('App\Helpers\Utils')

@props(['data'])

<div x-data="{ showDetails: true }" class="text-center">
    <div class="flex gap-4 justify-center pb-3 border-b border-slate-500">
        <label>
            <x-filament::input.checkbox x-model="showDetails" />

            <span>
                {{ __('global.reports.show_details') }}
            </span>
        </label>
        <label>
            <x-filament::input.checkbox x-model="showReductions" />

            <span>
                {{ __('global.reports.show_reductions') }}
            </span>
        </label>
    </div>
    @if ($data->isNotEmpty())
        <div class="mt-3 text-xl flex font-bold text-left">
            <div class="grow">{{ __('global.reports.total') }}</div>
            <div x-show="showReductions" class="flex-none text-right dark:text-red-300 text-red-600 w-24">
                {{ Utils::formatNumber(
                    $data->sum(fn($clients) => $clients->sum(fn($client) => $client->sum('service_price'))),
                    castMoney: false,
                 )}}
            </div>
            <div class="flex-none text-right w-24">
                {{ Utils::formatNumber(
                    $data->sum(fn($clients) => $clients->sum(fn($client) => $client->sum('price'))),
                    castMoney: false,
                )}}
            </div>
        </div>
    @endif
    <ul>
        @forelse($data as $date => $clients)
            <li class="flex flex-col pt-3 pb-1 text-left">
                <div class="flex grow font-bold">
                    <div class="grow">{{ Utils::formatDate($date) }}</div>
                    <div x-show="showReductions" class="text-right flex-none w-24 dark:text-red-300 text-red-600">
                        {{  Utils::formatNumber(
                            $clients->sum(fn($client) => $client->sum('service_price')),
                            castMoney: false,
                        )}}
                    </div>
                    <div class="flex-none text-right w-24">
                        {{Utils::formatNumber(
                            $clients->sum(fn($client) => $client->sum('price')),
                            castMoney: false,
                        )}}
                    </div>
                </div>
                <ul>
                    @foreach($clients as $clientId => $services)
                    <li class="flex grow flex-col pl-5">
                        <div :class="showDetails ? 'mb-2 mt-2' : ''" class="border-b border-slate-500 flex grow text-md">
                            <a class="grow text-blue-500" href="{{route('filament.admin.resources.clients.view', $clientId)}}">
                                <div>
                                    {{$services[0]["firstname"] ?? ""}}
                                    {{$services[0]["client_name"] ?? ""}}
                                </div>
                            </a>
                            <div x-show="showReductions" class="text-right flex-none w-24 dark:text-red-300 text-red-600">
                                {{Utils::formatNumber(
                                    $services->sum('service_price'),
                                    castMoney: false,
                                )}}
                            </div>
                            <div class="flex-none text-right w-24">
                                {{Utils::formatNumber(
                                    $services->sum('price'),
                                    castMoney: false,
                                )}}
                            </div>
                        </div>
                        <ul x-show="showDetails">
                            @foreach($services as $service)
                            <li class="flex grow pl-10 text-sm dark:text-slate-400 text-slate-500">
                                <div class="grow pr-2">{{$service["service_name"]}}</div>
                                <div x-show="showReductions" class="text-right flex-none w-24 dark:text-red-300 text-red-600">
                                    {{Utils::formatNumber(
                                        $service["service_price"],
                                        castMoney: false,
                                    )}}
                                </div>
                                <div class="flex-none text-right w-24">
                                    {{Utils::formatNumber(
                                        $service["price"],
                                        castMoney: false,
                                    )}}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach
                </ul>
            </li>
        @empty
            <li class="font-bold text-xl py-3">
                {{ __('global.reports.no_data') }}
            </li>
        @endforelse
    </ul>
</div>
