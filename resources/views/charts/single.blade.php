<x-app-layout>

    @connectible($connection)
        <livewire:area-filter :connection="$connection" />
    @else
        <x-simple-card>
            The area filter component is not available because the database connection called <b>{{$connection}}</b> is not available.
        </x-simple-card>
    @endconnectible

    <div class="grid grid-cols-1 gap-6 sm:p-6 sm:pt-0 pb-6 sm:pb-0 bg-gray-100">
        @connectible($metadata['connection'])
            <x-chart-card
                    page="{{$page}}"
                    chart="{{$chart}}"
                    mode="Full Page"
                    title="{{$metadata['title']}}"
                    description="{{$metadata['description']}}"
            >
                @livewire($chart, ['connection' => $metadata['connection'], 'graphDiv' => $chart, 'mode' => 'Full Page'])
            </x-chart-card>
        @else
            <x-simple-card>
                This indicator is not available because the database connection called <b>{{$connection}}</b> is not functioning.
            </x-simple-card>
        @endconnectible
    </div>

</x-app-layout>
