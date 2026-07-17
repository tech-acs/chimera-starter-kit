<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('You are about to enter the demo site. All data is read-only.') }}
        </div>

        <form method="POST" action="{{ route('login.demo') }}">
            @csrf

            <div class="block">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email"
                    value="{{ config('chimera.demo_email') }}" disabled />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button class="ms-4">
                    {{ __('Enter Demo Site') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
