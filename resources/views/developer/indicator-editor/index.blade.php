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
    <div class="h-[calc(100vh-12.5rem)] flex flex-col">

        <div class="bg-white border-b border-gray-200 shadow-sm px-4 py-3 flex items-center justify-between shrink-0">
            <div class="text-lg font-medium text-gray-900">
                {{ $indicator->title }}
            </div>
            <div class="flex items-center gap-2">
                <x-secondary-button type="button">
                    {{ __('Undo Changes') }}
                </x-secondary-button>
                <x-button type="button">
                    {{ __('Save as Template') }}
                </x-button>
            </div>
        </div>

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
