<div x-data="commandPalette()" x-cloak>
    <!-- Trigger -->
    <a class="cursor-pointer text-indigo-400" x-on:click="ready = true" title="{{ __('Search indicators') }}">
        <svg class="w-7 h-7 mt-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </a>

    <!-- Modal -->
    <div
        x-show="ready"
        x-trap="ready"
        x-on:keydown.escape.prevent.stop="ready = false"
        class="relative z-10"
        role="dialog"
        aria-modal="true"
    >
        <!--
          Background backdrop, show/hide based on modal state.

          Entering: "ease-out duration-300"
            From: "opacity-0"
            To: "opacity-100"
          Leaving: "ease-in duration-200"
            From: "opacity-100"
            To: "opacity-0"
        -->
        <!-- Overlay -->
        <div
            x-show="ready"
            x-on:click="ready = false"
            x-transition.opacity
            class="fixed inset-0 bg-gray-500 bg-opacity-25 transition-opacity"
        ></div>

        <div
            x-show="ready"
            x-on:click="ready = false"
            x-transition
            class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20"
        >
            <!--
              Command palette, show/hide based on modal state.

              Entering: "ease-out duration-300"
                From: "opacity-0 scale-95"
                To: "opacity-100 scale-100"
              Leaving: "ease-in duration-200"
                From: "opacity-100 scale-100"
                To: "opacity-0 scale-95"
            -->
            <div
                x-on:click.stop
                x-trap.noscroll.inert="open"
                class="mx-auto max-w-xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all"
            >
                <div class="relative">
                    <!-- Heroicon name: mini/magnifying-glass -->
                    <svg class="pointer-events-none absolute top-3.5 left-4 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input type="text"
                           wire:model.debounce.200ms="search"
                           @keyup.down.prevent="move('down')"
                           @keyup.up.prevent="move('up')"
                           @keyup.enter="a"
                           class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-800 placeholder-gray-400 focus:ring-0 sm:text-sm" placeholder="{{ __('Search indicators') }}..." role="combobox" aria-expanded="false" aria-controls="options">
                </div>

                <div class="py-14 px-6 text-center text-sm sm:px-14 self-center" wire:loading.block wire:loading.delay>
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <circle cx="15" cy="15" r="4"></circle>
                        <path d="M18.5 18.5l2.5 2.5"></path>
                        <path d="M4 6h16"></path>
                        <path d="M4 12h4"></path>
                        <path d="M4 18h4"></path>
                    </svg>
                    <p class="mt-4 font-semibold text-gray-900">Searching...</p>
                    <p class="mt-2 text-gray-500">Hold on while we search that for you.</p>
                </div>

                @if ($results->isEmpty())
                    <!-- Empty state, show/hide based on command palette state -->
                    <div class="py-14 px-6 text-center text-sm sm:px-14" wire:loading.remove>
                        <!-- Heroicon name: outline/exclamation-circle -->
                        <svg class="mx-auto h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <p class="mt-4 font-semibold text-gray-900">{{ __('No results found') }}</p>
                        <p class="mt-2 text-gray-500">{{ __('No results found for this search term. Please try another.') }}</p>
                    </div>
                @else
                    <ul class="max-h-96 scroll-py-3 overflow-y-auto p-3" id="options" role="listbox" wire:loading.remove>
                    @foreach($results as $result)
                        <!-- Active: "bg-gray-100" -->
                        <li wire:key="result-{{ $result->id }}" class="group flex cursor-default select-none rounded-xl p-3" role="option" tabindex="-1" :class="active === {{$loop->index}} ? 'bg-gray-100' : ''">
                            <a class="group flex" href="/indicator/{{ $result->slug }}" x-on:click.stop>
                                {{--<div class="flex h-10 w-10 flex-none items-center justify-center rounded-lg bg-indigo-100">
                                    <x-dynamic-component component="icon.line-chart" class="mt-4" />
                                </div>--}}
                                <div class="ml-2 flex-auto">
                                    <p class="text-sm font-medium" :class="active === {{$loop->index}} ? 'text-gray-900' : 'text-gray-700'">{{ $result->title }}</p>
                                    <p class="text-sm text-gray-500" :class="active === {{$loop->index}} ? 'text-gray-700' : 'text-gray-500'">{{ $result->description }}</p>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    <script>
        function commandPalette() {
            return {
                ready: false,
                active: 0,
                total: @entangle('resultCount'),
                move: function (dir) {
                    if (dir === 'up') {
                        this.active = Math.max(0, this.active - 1);
                    } else {
                        this.active = Math.min(this.total - 1, this.active + 1);
                    }
                }
            }
        }
    </script>
</div>
