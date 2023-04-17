@props(['indicator' => null])
<div class="bg-white shadow overflow-hidden sm:rounded-lg" x-data="{ show_help: false }">
    <div class="flex justify-between px-4 py-5 sm:px-6">
        <div class="w-full overflow-hidden">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ $indicator?->title }}
            </h3>
            <p class="mt-1 max-w-2xl truncate text-sm text-gray-500">
                {{$indicator?->description}}
            </p>
        </div>
        <div class="flex">
            @if($mode === 'grid')
                <div class="self-center">
                    <a href="{{route('indicator', $indicator?->slug)}}" title="Display chart full page" type="button" class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-400 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    </a>
                </div>
            @else
                <div class="self-center">
                    <a onclick="window.history.back();" title="Back" type="button" class="cursor-pointer inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-400 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                    </a>
                </div>
            @endif

            <livewire:exporter :indicator="$indicator" />

            <div class="self-center ml-2">
                <a @click="show_help = !show_help" title="Help" type="button" class="cursor-pointer inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-400 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </a>
            </div>
        </div>
    </div>
    <div class="border-t border-gray-200 relative" style="min-height: 491px;">
        {{ $slot }}
        <div
                x-show="show_help"
                x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="transition duration-1000 ease-in-out absolute inset-0 justify-center items-center opacity-90 bg-white px-4 py-5 sm:px-6"
                x-cloak
        >
            {!! $indicator?->help !!}
        </div>
    </div>
</div>
