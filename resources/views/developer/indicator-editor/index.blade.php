@pushonce('scripts')
    @viteReactRefresh
    @vite('resources/js/ChartEditor/index.jsx')
@endpushonce

<x-app-layout>

    <div class="relative pr-3" style="height: calc(100vh - 210px);">
        <div id="chart-editor" indicator="{{ $indicator->id }}" default-layout="{{ $defaultLayout }}"></div>
    </div>

</x-app-layout>
