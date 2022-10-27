<div class="shadow sm:rounded-md sm:overflow-hidden">
    {{--<div class="px-4 py-2 sm:px-6 bg-gray-50 border-b border-gray-200">
        <span class="text-xs text-gray-500 uppercase">
            {{ __('Create a new database connection') }}
        </span>
    </div>--}}
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div class="">
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-multi-lang-input disabled id="name" name="name" value="{{ $scorecard->name }}" class="bg-gray-50" />
                <x-jet-input-error for="name" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="title" value="{{ __('Title') }} *" />
                {{--<x-jet-input id="title" name="title" type="text" class="mt-1 block w-full" value="{{old('title', $stat->title ?? null)}}" />--}}
                <x-multi-lang-input id="title" name="title" value="{{old('title', $scorecard->title ?? null)}}" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="linked_indicator" value="{{ __('Linked Indicator') }}" />
                <select name="linked_indicator" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">None</option>
                    @foreach($indicators as $slug => $title)
                        <option value="{{ $slug }}" {{old('linked_indicator', $scorecard->linked_indicator ?? null) === $slug ? 'selected' : ''}}>{{ $title }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="linked_indicator" class="mt-2" />
            </div>
            {{--<div class="">
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <textarea name="description" rows="3" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $stat->description ?? null)}}</textarea>
                <x-jet-input-error for="description" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="help" value="{{ __('Contextual Help Text') }}" />
                <textarea name="help" rows="5" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $indicator->help ?? null)}}</textarea>
                <x-jet-input-error for="help" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="page_id" value="{{ __('Page') }}" />
                <select name="page_id" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($pages as $id => $pageTitle)
                        <option value="{{ $id }}" {{old('page_id', $indicator->page_id ?? null) === $id ? 'selected' : ''}}>{{ $pageTitle }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="page_id" class="mt-2" />
            </div>--}}
            <div class="">
                <x-jet-label for="page" value="{{ __('Status') }}" />
                <div class="flex items-center mt-3 ml-3" x-data="{enabled: @json($scorecard->published ?? false) }">
                    <span class="" id="annual-billing-label">
                        <span class="text-sm text-gray-500">{{ __('Draft') }}</span>
                    </span>
                    <input type="hidden" name="published" :value="enabled">
                    <button
                            x-on:click="enabled = ! enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            type="button"
                            class="ml-3  relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            role="switch"
                            aria-checked="false"
                            aria-labelledby="annual-billing-label"
                    >
                        <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                    </button>
                    <span class="ml-3" id="annual-billing-label">
                        <span class="text-sm text-gray-900">{{ __('Published') }}</span>
                    </span>
                </div>

            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
