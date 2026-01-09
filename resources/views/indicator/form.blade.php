<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" disabled name="name" type="text" class="mt-1 block w-full bg-gray-100" value="{{ $indicator->name }}" />
                <x-input-error for="name" class="mt-2" />
            </div>
            <div class="mt-1">
                <x-label for="title" value="{{ __('Title') }} *" />
                <x-chimera::multi-lang-input id="title" name="title" value="{{old('title', $indicator->title ?? null)}}" />
                <x-input-error for="title" class="mt-2" />
            </div>
            <div>
                <x-label for="description" value="{{ __('Description') }} *" class="inline" /><x-chimera::locale-display />
                <textarea name="description" rows="3" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $indicator->description ?? null)}}</textarea>
                <x-input-error for="description" class="mt-2" />
            </div>
            <div>
                <x-label for="help" value="{{ __('Contextual Help Text') }}" class="inline" /><x-chimera::locale-display />
                <textarea name="help" rows="5" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $indicator->help ?? null)}}</textarea>
                <x-input-error for="help" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <div>
                        <x-label for="pages" value="{{ __('Page') }}" />
                        <select name="pages[]" multiple class="space-y-1 text-base p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            <option disabled>{{ __('You can add the indicator to multiple pages') }}</option>
                            @foreach($pages as $id => $pageTitle)
                                <option class="p-2 rounded-md" value="{{ $id }}" @selected(in_array($id, $indicator->pages->pluck('id')->all()))>{{ $pageTitle }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="page_id" class="mt-2" />
                    </div>
                </div>
                <div class="col-span-1">
                    <div>
                        <x-label for="inapplicable_levels" value="{{ __('Unsupported area levels') }}" />
                        <select name="inapplicable_levels[]" multiple class="space-y-1 text-base p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            <option disabled>{{ __('You can select multiple levels') }}</option>
                            @foreach($areaHierarchies as $id => $name)
                                <option class="p-2 rounded-md" value="{{ $id }}" @selected(in_array($id, $indicator->inapplicableLevels->pluck('id')->all()))>{{ $name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="inapplicable_levels" class="mt-2" />
                    </div>
                </div>
            </div>


            <div>
                <x-label for="tag" value="{{ __('Cache Tag') }}" />
                <select name="tag" class="space-y-1 text-base p-1 pr-10 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                    <option value="">{{ __('Assign a cache tag') }}</option>
                    @foreach($tags as $tag)
                        <option class="p-2 rounded-md" value="{{ $tag }}" @selected($tag == $indicator->tag)>{{ $tag }}</option>
                    @endforeach
                </select>
                <x-input-error for="tag" class="mt-2" />
            </div>
            <div>
                <x-label for="page" value="{{ __('Status') }}" />
                <div class="flex items-center mt-3 ml-3" x-data="{enabled: @json($indicator->published ?? false) }">
                    <label for="status">
                        <span class="text-sm text-gray-500">{{ __('Draft') }}</span>
                    </label>
                    <input type="hidden" name="published" :value="enabled">
                    <button
                            x-on:click="enabled = ! enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            type="button"
                            class="ml-3 relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            role="switch"
                            id="status"
                    >
                        <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                    </button>
                    <label for="status" class="ml-3">
                        <span class="text-sm text-gray-900">{{ __('Published') }}</span>
                    </label>
                </div>
            </div>
            <div>
                <div class="relative flex items-start">
                    <div class="flex h-6 items-center mt-1">
                        <input @checked(old('is_featured', $indicator->featured_at ?? false)) id="is_featured" name="is_featured" type="checkbox" class="h-6 w-6 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                    </div>
                    <div class="ml-3 text-base leading-6">
                        <label for="is_featured" class="font-medium text-gray-900">Feature on home page</label>
                        <p class="text-gray-500 text-xs">{{ __('A maximum of :featured featured indicators will be displayed per data source', ['featured' => settings('featured_indicators_per_data_source')]) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-secondary-button onclick="window.history.back()" class="mr-2">{{ __('Cancel') }}</x-secondary-button>
        <x-button>
            {{ __('Submit') }}
        </x-button>
    </div>
</div>
