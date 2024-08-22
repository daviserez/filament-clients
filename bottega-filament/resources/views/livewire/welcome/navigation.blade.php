<div class="z-10 p-6 text-end fixed right-0 top-0">
    @auth
        <a class="font-semibold text-gray-600 hover:text-gray-900 focus:rounded-sm focus:outline focus:outline-2 focus:outline-red-500 dark:text-gray-400 dark:hover:text-white"
           href="{{ url('/admin') }}">
           {{ __('global.dashboard') }}
        </a>
    @else
        <a class="font-semibold text-gray-600 hover:text-gray-900 focus:rounded-sm focus:outline focus:outline-2 focus:outline-red-500 dark:text-gray-400 dark:hover:text-white"
           href="/admin/login"
           wire:navigate>{{ __('global.login') }}</a>

        @if (Route::has('register'))
            <a class="ms-4 font-semibold text-gray-600 hover:text-gray-900 focus:rounded-sm focus:outline focus:outline-2 focus:outline-red-500 dark:text-gray-400 dark:hover:text-white"
               href="{{ url('admin/register') }}"
               wire:navigate>{{ __('global.register') }}</a>
        @endif
    @endauth
</div>
