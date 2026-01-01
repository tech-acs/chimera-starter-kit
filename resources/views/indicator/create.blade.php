<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Indicators') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('You are creating a new indicator') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::error-display />

        <form action="{{ route('developer.indicator.store') }}" method="POST">
            @csrf

            <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="p-8 pt-2 bg-white">
                    <div class="grid grid-cols-1 space-y-8" x-data="{ chosenChartType: 'default', useTemplate: {{ old('use_template', 'false') }} }">

                        <input type="hidden" name="chosen_chart_type" :value="chosenChartType">

                        <div>
                            <x-label for="data_source" value="{{ __('Which data source will this indicator be using?') }} *" />
                            <select name="data_source" @change="if ($el.value != '') { $refs.indicator_name.value = $el.selectedOptions[0].text.concat('/'); $refs.indicator_name.focus(); } else { $refs.indicator_name.value = '' }" class="mt-1 space-y-1 text-base p-1 pr-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                <option value="">Select the data source</option>
                                @foreach($dataSources as $name => $title)
                                    <option class="p-2 rounded-md" value="{{ $name }}" @selected(old('data_source') == $name)>{{ $title }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="data_source" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="indicator_name" value="{{ __('Indicator name') }} *" />
                            <x-input value="{{ old('indicator_name') }}" x-ref="indicator_name" id="indicator_name" name="indicator_name" type="text" class="mt-1 block w-full lg:w-1/2" placeholder="E.g. HouseholdsEnumeratedByDay or Household/BirthRate" />
                            <x-chimera::input-hint-error for="indicator_name" class="mt-2">This will serve as the component name and has to be in camel case</x-chimera::input-hint-error>
                        </div>

                        @if ($availableTemplates->isNotEmpty())
                            <div>
                                <x-label for="use_template" value="{{ __('Do you want to create the indicator from a template?') }}" />
                                <div class="flex items-center mt-3" x-data="{enabled: useTemplate }">
                                    <label for="status">
                                        <span class="text-sm text-gray-500">{{ __('No') }}</span>
                                    </label>
                                    <input type="hidden" id="use_template" name="use_template" :value="enabled">
                                    <button
                                        x-on:click="enabled = ! enabled; if (enabled) { useTemplate = true; chosenChartType = 'template' } else { useTemplate = false; chosenChartType = 'default' };"
                                        :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                                        type="button"
                                        class="ml-3 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        role="switch"
                                        id="status"
                                    >
                                        <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                    </button>
                                    <label for="status" class="ml-3">
                                        <span class="text-sm text-gray-900">{{ __('Yes') }}</span>
                                    </label>
                                </div>
                                <x-chimera::input-hint-error for="use_template" class="mt-2">There are {{ $availableTemplates->count() }} templates to choose from</x-chimera::input-hint-error>
                            </div>

                            <div x-cloak x-show="useTemplate">
                                <x-label for="selectedTemplateId" value="{{ __('Select the indicator template you want to use') }} *" />
                                <select name="selectedTemplateId" class="mt-1 space-y-1 text-base p-1 pr-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                    <option value="">Select a template</option>
                                    @foreach($availableTemplates as $id => $name)
                                        <option class="p-2 rounded-md" value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error for="selectedTemplateId" class="mt-2" />
                            </div>
                        @endif

                        <div x-cloak x-show="chosenChartType === 'default'">
                            <x-label for="includeSampleCode" value="{{ __('Do you want the generated file to include functioning sample code?') }}" />
                            <div class="flex items-center mt-3" x-data="{enabled: {{ old('includeSampleCode', 'false') }} }">
                                <label for="sample">
                                    <span class="text-sm text-gray-500">{{ __('No') }}</span>
                                </label>
                                <input type="hidden" name="includeSampleCode" :value="enabled">
                                <button
                                    x-on:click="enabled = ! enabled"
                                    :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                                    type="button"
                                    class="ml-3 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    role="switch"
                                    id="sample"
                                >
                                    <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                </button>
                                <label for="sample" class="ml-3">
                                    <span class="text-sm text-gray-900">{{ __('Yes') }}</span>
                                </label>
                            </div>
                            <x-chimera::input-hint-error for="includeSampleCode" class="mt-2">The sample code will help you to develop your own indicator logic</x-chimera::input-hint-error>
                        </div>

                        <div class="mt-1 w-full lg:w-1/2">
                            <x-label for="title" value="{{ __('Please enter a reader friendly title for the indicator') }} *" />
                            <x-chimera::multi-lang-input id="title" name="title" value="{{ old('title') }}" />
                            <x-input-error for="title" class="mt-2" />
                        </div>

                        <div class="w-full lg:w-1/2">
                            <x-label for="description" value="{{ __('Please enter a description for the indicator') }} *" class="inline" /><x-chimera::locale-display />
                            <textarea name="description" rows="3" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $indicator->description ?? null)}}</textarea>
                            <x-input-error for="description" class="mt-2" />
                        </div>

                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <x-secondary-button onclick="window.history.back()" class="mr-2">{{ __('Cancel') }}</x-secondary-button>
                    <x-button type="submit">{{ __('Submit') }}</x-button>
                </div>
            </div>

        </form>

    </div>
</x-app-layout>
