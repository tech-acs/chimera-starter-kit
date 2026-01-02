<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        @foreach($dataSources as $dataSource)
            <x-chimera-summary :data-source="$dataSource" />
        @endforeach
    </div>

</x-app-layout>
