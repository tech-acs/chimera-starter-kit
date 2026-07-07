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
    <div class="h-[calc(100vh-12.5rem)] flex flex-col"
         x-data="{
             modalOpen: false,
             templateName: '',
             templateCategory: '',
             templateDescription: '',
             saving: false,
             error: '',

             async saveTemplate() {
                 this.saving = true;
                 this.error = '';
                 try {
                     const response = await fetch('/manage/developer/api/indicator/{{ $indicator->id }}');
                     if (!response.ok) throw new Error();
                     const { data, layout } = await response.json();

                     const postResponse = await fetch('/manage/developer/api/chart-template', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': '{{ csrf_token() }}'
                         },
                         body: JSON.stringify({
                             name: this.templateName,
                             category: this.templateCategory,
                             description: this.templateDescription,
                             data,
                             layout
                         })
                     });
                     if (!postResponse.ok) throw new Error();

                     this.modalOpen = false;
                     this.resetForm();
                     window.dispatchEvent(new CustomEvent('notify', {
                         detail: { type: 'success', content: 'Template saved successfully!' }
                     }));
                 } catch (e) {
                     this.error = 'Failed to save template. Please try again.';
                 } finally {
                     this.saving = false;
                 }
             },

             resetForm() {
                 this.templateName = '';
                 this.templateCategory = '';
                 this.templateDescription = '';
                 this.error = '';
             }
         }">

        <div class="bg-white border-b border-gray-200 shadow-sm px-4 py-3 flex items-center justify-between shrink-0">
            <div class="text-lg font-medium text-gray-900">
                {{ $indicator->title }}
            </div>
            <div class="flex items-center gap-2">
                <x-secondary-button type="button" @click="modalOpen = true">
                    {{ __('Save as Template') }}
                </x-secondary-button>
                <x-secondary-button type="button" @click="window.location.href = '{{ route('indicator.index') }}'">
                    {{ __('Close') }}
                </x-secondary-button>
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

        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ __('Save as Template') }}
                </h3>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('This will save the last synced chart state.') }}
                </p>

                <div class="space-y-4">
                    <div>
                        <x-label for="template_name" value="{{ __('Template name') }} *" />
                        <x-input x-model="templateName" id="template_name" name="template_name" type="text" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-label for="template_category" value="{{ __('Category') }}" />
                        <x-input x-model="templateCategory" id="template_category" name="template_category" type="text" class="mt-1 block w-full" />
                    </div>

                    <div>
                        <x-label for="template_description" value="{{ __('Description') }}" />
                        <textarea x-model="templateDescription" id="template_description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"></textarea>
                    </div>
                </div>

                <p x-show="error" x-text="error" class="mt-4 text-sm text-red-600"></p>

                <div class="mt-6 flex justify-end gap-2">
                    <x-secondary-button type="button" @click="modalOpen = false">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-button type="button" @click="saveTemplate()" x-bind:disabled="!templateName || saving">
                        <span x-show="saving">{{ __('Saving...') }}</span>
                        <span x-show="!saving">{{ __('Save Template') }}</span>
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
