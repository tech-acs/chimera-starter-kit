<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-jet-input id="name" disabled name="name" type="text" class="mt-1 block w-full bg-gray-100" value="{{ $indicator->name }}" />
                <x-jet-input-error for="name" class="mt-2" />
            </div>
            <div class="mt-1">
                <x-jet-label for="title" value="{{ __('Title') }} *" />
                <x-chimera::multi-lang-input id="title" name="title" value="{{old('title', $indicator->title ?? null)}}" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="description" value="{{ __('Description') }} *" class="inline" /><x-chimera::locale-display />
                <textarea name="description" rows="3" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $indicator->description ?? null)}}</textarea>
                <x-jet-input-error for="description" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="help" value="{{ __('Contextual Help Text') }}" class="inline" /><x-chimera::locale-display />
                <textarea name="help" rows="5" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $indicator->help ?? null)}}</textarea>
                <x-jet-input-error for="help" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="pages" value="{{ __('Page') }}" />
                <select name="pages[]" multiple class="space-y-1 text-base p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                    <option disabled>{{ __('You can add the indicator to multiple pages') }}</option>
                    @foreach($pages as $id => $pageTitle)
                        <option class="p-2 rounded-md" value="{{ $id }}" @selected(in_array($id, $indicator->pages->pluck('id')->all()))>{{ $pageTitle }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="page_id" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="tag" value="{{ __('Cache Tag') }}" />
                <select name="tag" class="space-y-1 text-base p-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                    <option value="">{{ __('Assign a cache tag') }}</option>
                    @foreach($tags as $tag)
                        <option class="p-2 rounded-md" value="{{ $tag }}" @selected($tag == $indicator->tag)>{{ $tag }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="tag" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="page" value="{{ __('Status') }}" />
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
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-secondary-button class="mr-2"><a href="{{ route('indicator.index') }}">{{ __('Cancel') }}</a></x-jet-secondary-button>
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
