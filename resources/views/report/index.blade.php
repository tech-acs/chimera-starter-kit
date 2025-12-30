<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        <x-chimera::message-display />

        <div class="mt-2 flex flex-col">
            <div class="inline-block min-w-full py-2 align-middle">

                <div class="px-4 xl:px-0">
                    <div class="mt-2 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($pages as $menu)
                            @can($menu['slug'])
                                <x-chimera::graphical-menu
                                    icon="reports"
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
        </div>
    </div>

</x-app-layout>
