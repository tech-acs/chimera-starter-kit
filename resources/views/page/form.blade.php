<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-jet-label for="title" value="{{ __('Title') }} *" />
                <x-chimera::multi-lang-input id="title" name="title" type="text" value="{{old('title', $page->title ?? null)}}" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="description" value="{{ __('Description') }}" class="inline" /><x-chimera::locale-display />
                <x-chimera::textarea name="description" rows="3">{{old('description', $page->description ?? null)}}</x-chimera::textarea>
                <x-jet-input-error for="description" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="rank" value="{{ __('Rank') }}" />
                <x-jet-input id="rank" name="rank" type="number" class="w-20 mt-1" value="{{ old('rank', $page->rank ?? null) }}" />
                <x-jet-input-error for="rank" class="mt-2" />
            </div>
            <div>
                <x-jet-label for="published" value="{{ __('Status') }}" />
                <div class="flex items-center mt-3 ml-3" x-data="{enabled: @json($page->published ?? false) }" x-cloak>
                    <label for="status">
                        <span class="text-sm text-gray-500">Draft</span>
                    </label>
                    <input type="hidden" name="published" :value="enabled">
                    <button
                            x-on:click="enabled = ! enabled"
                            :class="enabled ? 'bg-indigo-600' : 'bg-gray-200'"
                            type="button"
                            class="ml-3  relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
            @if (isset($page))
                <div class="">
                    <x-jet-label for="indicators" value="{{ __('Indicators on page') }}" />

                    <div class="w-1/2 mt-2 px-2 border border-gray-300 rounded-md">
                        <ul role="list" class="divide-y divide-gray-200 -mt-[1px]" x-data="indicatorOrdering()">
                            <template x-if="indicators.length === 0">
                                <li class="flex py-4">
                                    <div class="">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ __('You have not added any indicators to this page') }}
                                        </p>
                                    </div>
                                </li>
                            </template>
                            <template x-for="(indicator, i) in indicators" :key="indicator.id">
                                <li class="flex justify-between py-4">
                                    <div class="ml-3">
                                        <p x-text="indicator.title" class="text-sm font-medium text-gray-900"></p>
                                        <input type="hidden" name="indicators[]" x-model="indicator.id">
                                    </div>
                                    <div class="flex mr-3">
                                        <a class="cursor-pointer" x-on:click="moveUp(i)">
                                            <svg class="w-6 h-6 text-blue-700" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path></svg>
                                        </a>
                                        <a class="cursor-pointer ml-1" x-on:click="moveDown(i)">
                                            <svg class="w-6 h-6 text-blue-700" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path></svg>
                                        </a>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-secondary-button class="mr-2"><a href="{{ route('page.index') }}">{{ __('Cancel') }}</a></x-jet-secondary-button>
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
<script>
    function indicatorOrdering() {
        return {
            indicators: [],
            init () {
                this.indicators = @json(isset($page) ? $page->indicators->map(fn ($i) => ['id' => $i->id, 'title' => $i->title]) : [])
            },
            moveUp(index) {
                if (index > 0) {
                    let overtaken = this.indicators.at(index - 1);
                    this.indicators[index - 1] = this.indicators[index];
                    this.indicators[index] = overtaken;
                }
            },
            moveDown(index) {
                if (index < this.indicators.length - 1) {
                    let promoted = this.indicators.at(index + 1);
                    this.indicators[index + 1] = this.indicators[index];
                    this.indicators[index] = promoted;
                }
            }
        }
    }
</script>
