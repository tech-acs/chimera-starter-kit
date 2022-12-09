@push('scripts')
    @vite(['resources/js/chart.js'])
@endpush

<x-app-layout>

    <livewire:area-filter />

    <div class="grid grid-cols-1 gap-6 sm:p-6 sm:pt-0 pb-6 sm:pb-0 bg-gray-100">
        @connectible($indicator->questionnaire)
            <x-chimera-chart-card :indicator="$indicator" mode="Full Page">
                @livewire($indicator->component, ['indicator' => $indicator])
            </x-chimera-chart-card>
        @else
            <x-chimera-simple-card>
                This indicator is not available because the database connection of the questionnaire
                called <b>{{ $indicator->questionnaire }}</b> is not available.
            </x-chimera-simple-card>
        @endconnectible
    </div>

</x-app-layout>
