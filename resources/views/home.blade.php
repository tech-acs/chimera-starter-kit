<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        @foreach($dataSources as $dataSource)
            <x-chimera-summary :data-source="$dataSource" />
        @endforeach

        <div class="px-4 xl:px-0">
            <div class="mt-2 grid grid-cols-1 gap-8 lg:grid-cols-3">
                @foreach($graphicalMenu as $menu)
                    @can($menu['slug'])
                        <x-chimera::graphical-menu
                            :title="$menu['title']"
                            :description="$menu['description']"
                            :link="$menu['link']"
                            :bg-color="$menu['bg-color']"
                            :fg-color="$menu['fg-color']"
                        />
                    @endcan
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>
