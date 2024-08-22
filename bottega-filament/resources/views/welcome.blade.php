<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1"
          name="viewport">

    <title>La Bottega</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net"
          rel="preconnect">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
          rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">
    @if (Route::has('login'))
        <livewire:welcome.navigation />
    @endif
    <div
         class="bg-dots-darker dark:bg-dots-lighter relative min-h-screen bg-gray-100 bg-center selection:bg-red-500 selection:text-white dark:bg-gray-900 flex flex-row items-center justify-center">

        <div class="flex sm:w-6/12 w-10/12 flex-col gap-6 text-center dark:text-white">
            <img alt="logo"
                 class="hidden dark:block sm:mb-0 mb-10"
                 src="{{ URL::asset('images/logo-big-dark.svg') }}">
            <img alt="logo"
                 src="{{ URL::asset('images/logo-big.svg') }}"
                 class="dark:hidden block sm:mb-0 mb-10">
            <address class="not-italic">
                <a href="https://maps.app.goo.gl/zbdzJTLzjKdnKcLU8"
                   target="_blank"
                   class="hover:underline">
                    Chem. de la Brume 2<br>
                    1110 Morges<br>

                </a>
            </address>
            <div>
                <a href="tel:+41218026677" class="flex justify-center gap-3 hover:underline">
                    021 / 802 66 77
                </a>
            </div>
        </div>
    </div>
</body>

</html>
