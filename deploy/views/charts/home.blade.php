<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">

        @forelse($indicators as $connection => $subIndicators)
            @livewire('home.summary', ['connection' => $connection, 'subIndicators' => $subIndicators->all()])
        @empty
            There are no indicators to display
        @endforelse

    </div>

</x-app-layout>
