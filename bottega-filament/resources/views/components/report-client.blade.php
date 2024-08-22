@use('App\Helpers\Utils')

@props(['data'])

<div class="text-center">
    <div class="flex gap-4 justify-center pb-3 border-b border-slate-500">
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
                {{Utils::formatNumber(
                    $data->sum(fn($clients) => $clients->sum('service_price')),
                    castMoney: false,
                )}}
            </div>
            <div class="flex-none text-right w-24">
                {{Utils::formatNumber(
                    $data->sum(fn($clients) => $clients->sum('price')),
                    castMoney: false,
                )}}
            </div>
        </div>
    @endif
    <ul>
        @forelse($data as $clientId => $clients)
            <li class="flex grow flex-col text-left">
                <div class="border-b border-slate-500 flex grow text-md mt-3">
                    <a class="grow text-blue-500" href="{{route('filament.admin.resources.clients.view', $clientId)}}">
                        <div>
                            {{$clients[0]["firstname"] ?? ""}}
                            {{$clients[0]["client_name"] ?? ""}}
                        </div>
                    </a>
                    <div x-show="showReductions" class="text-right flex-none w-24 dark:text-red-300 text-red-600">
                        {{Utils::formatNumber(
                            $clients->sum('service_price'),
                            castMoney: false,
                        ) }}
                    </div>
                    <div class="flex-none text-right w-24">
                        {{Utils::formatNumber(
                            $clients->sum('price'),
                            castMoney: false,
                        )}}
                    </div>
                </div>
                <ul class="mt-2">
                    @foreach($clients as $service)
                    <li class="flex grow pl-5 text-sm dark:text-slate-400 text-slate-500">
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
        @empty
            <li class="font-bold text-xl py-3">
                {{ __('global.reports.no_data') }}
            </li>
        @endforelse
    </ul>
</div>
