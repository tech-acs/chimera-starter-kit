<div>
    <div class="flex justify-between">
        <div>
            @if($smartTableData->searchableColumns->isNotEmpty())
                <form method="get" action="{{ route($smartTableData->request->route()->getName()) }}">
                    <x-input type="search" name="search" placeholder="{{ $smartTableData->searchPlaceholder }}" value="{{ request('search') }}" />
                    <div class="text-xs text-gray-400 ml-1">{{ $smartTableData->searchHint }}</div>
                    <button type="submit" class="sm:hidden rounded-md bg-white px-2.5 py-1 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Search</button>
                </form>
            @endif
        </div>
        <div class="flex items-center">
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
            <div class="ml-4">
                <a href="{{ request()->fullUrlWithQuery(['download' => true]) }}">
                    <x-secondary-button title="Download current results as a CSV file"><x-chimera::icon.csv /></x-secondary-button>
                </a>
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 tracking-wider">
                                    {!! $column->getLabel() !!} <a href="?sort_by={{ $column->attribute }}&sort_direction={{ $column->reverseSortDirection() }}">{!! $column->sortIcon() !!}</a>
                                </th>
                            @endforeach
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($smartTableData->rows as $row)
                            <tr>
                                @foreach($smartTableData->columns as $column)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {!! Blade::render($column->getBladeTemplate(), compact('row', 'column')) !!}
                                    </td>
                                @endforeach
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium items-center">
                                    @if ($customActionSubView)
                                        @include($customActionSubView)
                                    @elseif(isset($smartTableData->editRouteName))
                                        <a href="{{ route($smartTableData->editRouteName, $row->id) }}" class="text-indigo-600 hover:text-indigo-900 inline">{{ __('Edit') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
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
