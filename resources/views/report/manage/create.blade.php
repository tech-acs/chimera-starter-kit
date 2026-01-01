<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Reports') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('You are creating a new report') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::error-display />

        <form action="{{ route('developer.report.store') }}" method="POST">
            @csrf

            <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="p-8 pt-2 bg-white">
                    <div class="grid grid-cols-1 space-y-8" x-data>
                        <span></span>

                        <div>
                            <x-label for="data_source" value="{{ __('Which data source will this report be using?') }} *" />
                            <select name="data_source" @change="if ($el.value != '') { $refs.report_name.value = $el.selectedOptions[0].text.concat('/'); $refs.report_name.focus(); } else { $refs.report_name.value = '' }" class="mt-1 space-y-1 text-base p-1 pr-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                <option value="">Select the data source</option>
                                @foreach($dataSources as $name => $title)
                                    <option class="p-2 rounded-md" value="{{ $name }}" @selected(old('data_source') == $name)>{{ $title }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="data_source" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="scorecard_name" value="{{ __('Report name') }} *" />
                            <x-input value="{{ old('report_name') }}" x-ref="report_name" id="report_name" name="report_name" type="text" class="mt-1 block w-full lg:w-1/2" placeholder="E.g. HouseholdsEnumeratedByDay or Household/BirthRate" />
                            <x-chimera::input-hint-error for="report_name" class="mt-2">This will serve as the component name and has to be in camel case</x-chimera::input-hint-error>
                        </div>

                        <div class="mt-1 w-full lg:w-1/2">
                            <x-label for="title" value="{{ __('Please enter a reader friendly title for the report') }} *" />
                            <x-chimera::multi-lang-input id="title" name="title" value="{{ old('title') }}" />
                            <x-input-error for="title" class="mt-2" />
                        </div>

                        <div class="w-full lg:w-1/2">
                            <x-label for="description" value="{{ __('Please enter a description for the report') }}" class="inline" /><x-chimera::locale-display />
                            <textarea name="description" rows="3" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description')}}</textarea>
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
