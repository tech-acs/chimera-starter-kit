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
            <label class="text-sm text-gray-600 mr-2">Results per page</label>
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
                    @foreach([10, 20, 30, 40, 50, 75, 100] as $pageSize)
                        <x-dropdown-link href="?page_size={{ $pageSize }}">
                            {{ $pageSize }}
                        </x-dropdown-link>
                    @endforeach
                </x-slot>
            </x-dropdown>
        </div>
    </div>

    @if (session('message'))
        <div class="rounded-md p-4 py-3 mt-4 mb-4 border bg-blue-50 border-blue-300">
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: solid/information-circle -->
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        {{session('message')}}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-md p-4 py-3 mt-4 mb-4 border bg-red-100 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: solid/information-circle -->
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between text-sm text-red-700">
                    <ul class="">
                        @foreach($errors->all() as $error)
                            <li class="list-disc">{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

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
                                    {!! $column->getLabel() !!} <a href="?sort_by={{ $column->attribute }}">{!! $column->sortIcon() !!}</a>
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
                                    @else
                                        <a href="{{ route('indicator.edit', $row->id) }}" class="text-indigo-600 hover:text-indigo-900 inline">{{ __('Edit') }}</a>
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
