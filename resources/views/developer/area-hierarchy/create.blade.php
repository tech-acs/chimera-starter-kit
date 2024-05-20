<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Area Hierarchies') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Creating a new area hierarchy') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::error-display />

        <form action="{{route('developer.area-hierarchy.store')}}" method="POST">
            @csrf
            @include('chimera::developer.area-hierarchy.form')
        </form>

    </div>
</x-app-layout>
