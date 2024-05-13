<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        @forelse($dataSources as $dataSource)
            <x-chimera-summary :data-source="$dataSource" />
        @empty
            <x-chimera-simple-card>
                {{ __('There are no data sources to display.') }}
            </x-chimera-simple-card>
        @endforelse

        <div class="px-4 xl:px-0">
            <div class="mt-2 grid grid-cols-1 gap-8 lg:grid-cols-3">
                @foreach($graphicalMenu as $menu)
                    <x-chimera::graphical-menu
                        title="{{ $menu['title'] }}"
                        description="{{ $menu['description'] }}"
                        link="{{ $menu['link'] }}"
                        color="{{ \Uneca\Chimera\Services\ColorPalette::current()->colors[$loop->index] }}"
                    />
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>
