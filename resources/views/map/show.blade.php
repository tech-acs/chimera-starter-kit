@push('scripts')
    @vite(['resources/css/map.css', 'resources/js/map.js'])
@endpush

<x-app-layout>
    <livewire:map :page="$page" />
</x-app-layout>
