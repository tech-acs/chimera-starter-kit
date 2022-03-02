<style>
    .rotate-45 {
        --transform-rotate: 45deg;
        transform: rotate(45deg);
    }
    .group:hover .group-hover\:flex {
        display: flex;
    }
</style>

<nav class="border-t border-b border-gray-200 px-4 flex items-center justify-between sm:px-0 bg-white pb-4">
    <div class="-mt-px ml-1 w-0 flex-1 flex">
        @if ($paginator->onFirstPage())
            <div class="border-transparent pt-4 pr-1 inline-flex items-center text-sm font-medium text-gray-300">
                <svg class="mr-3 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                {{ __('Previous') }}
            </div>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="border-t-2 border-transparent pt-4 pr-1 inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                <!-- Heroicon name: solid/arrow-narrow-left -->
                <svg class="mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                {{ __('Previous') }} 
            </a>
        @endif
    </div>

    <div class="hidden md:-mt-px md:flex">
        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="border-transparent text-gray-500 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">{{$element}}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="border-indigo-500 text-indigo-600 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium" aria-current="page">
                            {{$page}}
                        </span>
                    @else
                        <div class="relative flex flex-col items-center group overflow">
                            <a href="{{ $url }}" class="cursor-pointer border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium">
                                {{$page}}
                            </a>
                            <div class="absolute bottom-0 flex flex-col items-center whitespace-nowrap hidden mb-6 group-hover:flex">
                                <span class="relative z-10 p-2 text-sm leading-none text-white whitespace-no-wrap bg-indigo-700 shadow-lg rounded-md">
                                    <ul class="list-disc px-4 leading-6">
                                        @foreach($preview[$page - 1] as $indicator)
                                            <li>{{$indicator}}</li>
                                        @endforeach
                                    </ul>
                                </span>
                                <div class="w-3 h-3 -mt-2 rotate-45 bg-indigo-700"></div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        @endforeach
    </div>

    <div class="-mt-px w-0 mr-1 flex-1 flex justify-end">
        @if ($paginator->hasMorePages())
            <a href="{{$paginator->nextPageUrl()}}" class="border-t-2 border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                {{ __('Next') }}
                <!-- Heroicon name: solid/arrow-narrow-right -->
                <svg class="ml-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        @else
            <div class="border-transparent pt-4 pl-1 inline-flex items-center text-sm font-medium text-gray-300">
                {{ __('Next') }}
                <!-- Heroicon name: solid/arrow-narrow-right -->
                <svg class="ml-3 h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif
    </div>
</nav>

