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
                <x-jet-label for="questionnaire" value="{{ __('Questionnaire') }}" />
                <select name="questionnaire" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($questionnaires as $questionnaire)
                        <option value="{{ $questionnaire }}" {{old('questionnaire', $questionnaire ?? null) ? 'selected' : ''}}>{{ $questionnaire }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="questionnaire" class="mt-2" />
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
