<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div class="">
                <x-jet-label for="title" value="{{ __('Title') }}" />
                <x-jet-input id="title" name="title" type="text" class="mt-1 block w-full" value="{{old('title', $page->title ?? null)}}" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <textarea name="description" rows="5" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $page->description ?? null)}}</textarea>
                <x-jet-input-error for="description" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="published" value="{{ __('Status') }}" />
                <div class="flex items-center mt-3 ml-3" x-data="{enabled: @json($page->published ?? false) }">
                    <span class="">
                        <span class="text-sm text-gray-500">Draft</span>
                    </span>
                    <input type="hidden" name="published" :value="enabled">
                    <button
                            x-on:click="enabled = ! enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            type="button"
                            class="ml-3  relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            role="switch"
                    >
                        <span aria-hidden="true" :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                    </button>
                    <span class="ml-3">
                        <span class="text-sm text-gray-900">{{ __('Published') }}</span>
                    </span>
                </div>

            </div>
            {{--<div class="">
                <x-jet-label for="questionnaire" value="{{ __('Questionnaire') }}" />
                <select name="questionnaire" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($questionnaires as $questionnaire)
                        <option value="{{ $questionnaire }}" {{old('questionnaire', $questionnaire ?? null) ? 'selected' : ''}}>{{ $questionnaire }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="questionnaire" class="mt-2" />
            </div>--}}
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
