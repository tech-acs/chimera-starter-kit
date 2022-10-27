<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div class="">
                <x-jet-label for="title" value="{{ __('Title') }}" />
                <x-multi-lang-input id="title" name="title" type="text" value="{{old('title', $page->title ?? null)}}" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <x-multi-lang-textarea name="description" rows="3">{{old('description', $page->description ?? null)}}</x-multi-lang-textarea>
                <x-jet-input-error for="description" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="published" value="{{ __('Status') }}" />
                <div class="flex items-center mt-3 ml-3" x-data="{enabled: @json($page->published ?? false) }" x-cloak>
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
            @if (isset($page))
                <div class="">
                    <x-jet-label for="indicators" value="{{ __('Indicators on page') }}" />

                    <div class="w-1/2 mt-2 px-2 border border-gray-300 rounded-md">
                        <ul role="list" class="divide-y divide-gray-200">
                            @forelse($page->indicators as $indicator)
                                <li class="flex py-4">
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $indicator->title }}</p>
                                    </div>
                                </li>
                            @empty
                                <li class="flex py-4">
                                    <div class="">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ __('You have not added any indicators to this page') }}
                                        </p>
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
