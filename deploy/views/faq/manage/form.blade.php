<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-2 sm:px-6 bg-gray-50 border-b border-gray-200">
        <span class="text-xs text-gray-500 uppercase">
            {{ __('Create a new FAQ') }}
        </span>
    </div>
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div class="">
                <x-jet-label for="question" value="{{ __('Question') }}" />
                <x-jet-input id="question" name="question" type="text" class="mt-1 block w-full" value="{{old('question', $faq->question ?? null)}}" />
                <x-jet-input-error for="question" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="answer" value="{{ __('Answer') }}" />
                <textarea name="answer" rows="5" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('answer', $faq->answer ?? null)}}</textarea>
                <x-jet-input-error for="answer" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="rank" value="{{ __('Rank') }}" />
                <x-jet-input id="rank" name="rank" type="text" class="mt-1 block" value="{{old('rank', $faq->rank ?? null)}}" />
                <x-jet-input-error for="rank" class="mt-2" />
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
