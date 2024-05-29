<x-app-layout>

    {{--<main class="flex-grow pb-6 pr-4">--}}
        <div class="relative" style="height: calc(100vh - 210px);">
            <div id="chart-editor" indicator="{{ $indicator->id }}" default-layout="{{ json_encode(Uneca\Chimera\Livewire\Chart::getDefaultLayout()) }}"></div>
        </div>
    {{--</main>--}}

</x-app-layout>
