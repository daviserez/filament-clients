<?php

use App\Livewire\Forms\LoginForm;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirect(session('url.intended', RouteServiceProvider::HOME), navigate: true);
};

?>

<div>
    <!-- Session Status -->
    <x-auth-session-status :status="session('status')"
                           class="mb-4" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label :value="__('Email')"
                           for="email" />
            <x-text-input autocomplete="username"
                          autofocus
                          class="mt-1 block w-full"
                          id="email"
                          name="email"
                          required
                          type="email"
                          wire:model="form.email" />
            <x-input-error :messages="$errors->get('email')"
                           class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label :value="__('Password')"
                           for="password" />

            <x-text-input autocomplete="current-password"
                          class="mt-1 block w-full"
                          id="password"
                          name="password"
                          required
                          type="password"
                          wire:model="form.password" />

            <x-input-error :messages="$errors->get('password')"
                           class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mt-4 block">
            <label class="inline-flex items-center"
                   for="remember">
                <input class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                       id="remember"
                       name="remember"
                       type="checkbox"
                       wire:model="form.remember">
                <span
                      class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                   href="{{ route('password.request') }}"
                   wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>
