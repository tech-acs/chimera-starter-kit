<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        @forelse($dataSources as $dataSource)
            <x-chimera-summary :data-source="$dataSource">
                @forelse($dataSource->scorecards as $scorecard)
                    @livewire('scorecard.' . $scorecard->slug, ['scorecard' => $scorecard, 'index' => $loop->index])
                @empty
                    {{ __('There are no scorecards to display.') }}
                @endforelse
            </x-chimera-summary>
        @empty
            <x-chimera-simple-card>
                {{ __('There are no data sources to display.') }}
            </x-chimera-simple-card>
        @endforelse

        <div class="px-4 xl:px-0">
            <div class="mt-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
                @foreach($graphicalMenu as $menu)
                    <x-chimera::graphical-menu
                        title="{{ $menu['title'] }}"
                        description="{{ $menu['description'] }}"
                        link="{{ $menu['link'] }}"
                        image="{{ $menu['image'] }}"
                    />
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>
