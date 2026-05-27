@if(! app()->isProduction())
    @pushonce('styles')
        @plotlyChartEditorStyles
    @endpushonce
    @pushonce('scripts')
        @plotlyChartEditorScripts
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('chart-synced', ({ data, layout }) => {
                    fetch('/manage/developer/api/indicator/{{ $indicator->id }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ data, layout }),
                    });
                });
            });
        </script>
    @endpushonce
@endif
<x-app-layout>
    <div class="h-[calc(100vh-4rem)] flex flex-col">
        <livewire:plotly-editor
            :data-sources="$dataSources"
            :data="$data"
            :layout="$layout"
            :config="$config"
            :trace-types="['bar', 'scatter', 'pie', 'histogram', 'line', 'area', 'box', 'sunburst']"
            sync-mode="manual"
            :preload-schema="true"
            :show-export="true"
        />
    </div>
</x-app-layout>
