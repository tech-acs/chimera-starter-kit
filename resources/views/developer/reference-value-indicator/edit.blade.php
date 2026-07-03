<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Reference Value Indicators') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Editing an existing indicator') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::error-display />

        <form action="{{route('developer.reference-value-indicator.update', $referenceValueIndicator)}}" method="POST">
            @csrf
            @method('PATCH')

            <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                    <div class="w-1/2">
                        <x-label for="indicator" value="{{ __('Indicator') }}" />
                        <x-input name="indicator" class="mt-1 w-full" value="{{ $referenceValueIndicator->indicator }}" disabled />
                    </div>
                    <div class="w-1/2">
                        <x-label for="description" value="{{ __('Description') }} *" />
                        <x-input name="description" class="mt-1 w-full" value="{{ old('description', $referenceValueIndicator->description) }}" autofocus />
                        <x-input-error for="description" class="mt-2" />
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <x-secondary-button class="mr-2"><a href="{{ route('developer.reference-value-indicator.index') }}">{{ __('Cancel') }}</a></x-secondary-button>
                    <x-button>
                        {{ __('Submit') }}
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
