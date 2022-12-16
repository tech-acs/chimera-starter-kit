<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Usage Stats') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">{{ __('Here you will find usage stats of all users.
            You can then assign users one of the roles you have setup which will then dictate which features they will have access to.') }}</p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 pt-2 px-4 sm:px-6 lg:px-8">

        <div class="py-2 align-middle inline-block min-w-full">
            <div class="flex justify-end mb-2 text-sm items-center">
                @if (!blank($filter))
                    {{ __('Showing only') }} &nbsp;
                    <span class="inline-flex rounded-full items-center py-0.5 pl-2.5 pr-1 text-sm font-medium bg-indigo-100 text-indigo-700">
                      {{$filter}}
                      <a href="{{route('usage_stats')}}" type="button" class="cursor-pointer flex-shrink-0 ml-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white">
                        <span class="sr-only">{{ __('Remove large option') }}</span>
                        <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                          <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                        </svg>
                      </a>
                    </span>
                @endif
            </div>
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Who') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('What') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('When') }}
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{$record->user->name}} (<a href="?filter=email:{{$record->user->email}}" class="text-blue-600 cursor-pointer">{{$record->user->email}}</a>)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="?filter=event:{{$record->event}}" class="text-blue-600 cursor-pointer">{{$record->event}}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{$record->created_at->locale(app()->getLocale())->isoFormat('llll')}}
                                    </td>
                                </tr>
                            @empty

                            @endforelse
                            </tbody>
                            @if ($records->hasPages())
                            <tfoot>
                                <tr><td colspan="3" class="px-6 text-left text-xs text-gray-500  tracking-wider">{{ $records->appends(request()->all())->links() }}</td></tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-app-layout>
