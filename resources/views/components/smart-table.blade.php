<div>
    <div class="flex justify-between">
        <div>
            @if($smartTableData->searchableColumns->isNotEmpty())
                <form method="get" action="{{ route($smartTableData->request->route()->getName()) }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_direction" value="{{ request('sort_direction') }}">
                    <x-input type="search" name="search" placeholder="{{ $smartTableData->searchPlaceholder }}" value="{{ request('search') }}" />
                    <div class="text-xs text-gray-400 ml-1">{{ $smartTableData->searchHint }}</div>
                    <button type="submit" class="sm:hidden rounded-md bg-white px-2.5 py-1 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Search</button>
                </form>
            @endif
        </div>
        <div class="flex flex-col md:flex-row items-center">
            <label class="text-sm text-gray-600 mr-2">Per page</label>
            <x-dropdown align="right" width="20">
                <x-slot name="trigger">
                    <span class="inline-flex rounded-md border border-gray-300">
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                            {{ session()->get('page_size', $smartTableData->defaultPageSize) }}
                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </span>
                </x-slot>

                <x-slot name="content">
                    @foreach($pageSizeOptions as $pageSize)
                        <x-dropdown-link href="?page_size={{ $pageSize }}">
                            {{ $pageSize }}
                        </x-dropdown-link>
                    @endforeach
                </x-slot>
            </x-dropdown>

            @if($smartTableData->isDownloadable)
                {{--<div class="ml-4">
                    <a href="{{ request()->fullUrlWithQuery(['download' => true]) }}">
                        <x-secondary-button title="Download current results as a CSV file"><x-chimera::icon.csv /></x-secondary-button>
                    </a>
                </div>--}}
                <div class="relative" x-data="{ open: false }">
                    <div class="inline-flex divide-x divide-gray-200 rounded-md shadow-sm ml-4 border border-gray-300">
                        <a href="{{ request()->fullUrlWithQuery(['download' => 'all']) }}" title="Download all records as a CSV file">
                            <button class="inline-flex items-center gap-x-1.5 rounded-l-md bg-white px-3 py-2 text-blue-600 shadow-sm hover:text-blue-400">
                                <x-chimera::icon.download />
                                <p class="text-xs tracking-widest font-semibold">ALL</p>
                            </button>
                        </a>
                        <button @click="open = ! open" @click.outside="open = false" type="button" class="inline-flex items-center rounded-l-none rounded-r-md bg-white p-2 hover:text-blue-400" aria-haspopup="listbox" aria-expanded="true" aria-labelledby="listbox-label">
                            <svg class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <ul x-cloak x-show="open" class="absolute right-0 z-10 mt-1 origin-top-right divide-y divide-gray-200 overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" tabindex="-1" role="listbox" aria-labelledby="listbox-label" aria-activedescendant="listbox-option-0">
                        <a href="{{ request()->fullUrlWithQuery(['download' => 'filtered']) }}" title="Download filtered (current) records as a CSV file">
                            <button class="inline-flex items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-blue-600 shadow-sm hover:text-blue-400">
                                <x-chimera::icon.download />
                                <p class="text-xs tracking-widest font-semibold">FILTERED</p>
                            </button>
                        </a>
                    </ul>
                </div>
            @endif

        </div>
    </div>

    <div class="mt-2 flex flex-col">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg" x-data="confirmedDeletion">

                    <x-chimera::delete-confirmation prompt="[ This action is irreversible ]" />

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            @foreach($smartTableData->columns as $column)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider text-nowrap">
                                    {!! $column->getLabel() !!} <a href="?sort_by={{ $column->attribute }}&sort_direction={{ $column->reverseSortDirection() }}&search={{ request('search') }}">
                                        {!! $column->sortIcon() !!}
                                    </a>
                                </th>
                            @endforeach
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($smartTableData->rows as $row)
                            <tr>
                                @foreach($smartTableData->columns as $column)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 {{ $column->classes }}">
                                        {!! Blade::render($column->getBladeTemplate(), compact('row', 'column')) !!}
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium items-center">
                                    @if ($customActionSubView)
                                        @include($customActionSubView)
                                    @else
                                        <div class="flex justify-end divide-x-2 divide-gray-400 h-4 text-sm">
                                            @isset($smartTableData->showRouteName)
                                                <a href="{{ route($smartTableData->showRouteName, $row->id) }}" class="text-gray-600 hover:text-grey-900 px-2">{{ __('View') }}</a>
                                            @endisset
                                            @isset($smartTableData->editRouteName)
                                                <a href="{{ route($smartTableData->editRouteName, $row->id) }}" class="text-indigo-600 hover:text-indigo-900 px-2">{{ __('Edit') }}</a>
                                            @endisset
                                            @isset($smartTableData->deleteRouteName)
                                                <a href="{{ route($smartTableData->deleteRouteName, $row->id) }}" x-on:click.prevent="confirmThenDelete($el)" class="text-red-600 hover:text-red-800 px-2">{{ __('Delete') }}</a>
                                            @endisset
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $smartTableData->columns->count() + 1 }}" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                    {{ __('There are no records to display') }}
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if ($smartTableData->rows->hasPages())
                        <tfoot>
                            <tr class="bg-gray-50"><td colspan="6" class="px-6 py-2 text-left text-xs text-gray-500 tracking-wider">{{ $smartTableData->rows->withQueryString()->links() }}</td></tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
