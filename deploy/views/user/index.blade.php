<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('User management') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">{{ __('Users can sign-up when they receive their unique registration link (invite).') }}
            {{ __('You can then assign users one of the roles you have setup which will then dictate which features they will have access to.') }}</p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Title') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Created') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Role') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Edit</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $record->profile_photo_url }}" alt="{{ $record->name }}" />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{$record->name}}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{$record->email}}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{$record->title}}</div>
                                    <div class="text-sm text-gray-500">{{$record->organization}}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm">
                                {{$record->created_at->toFormattedDateString()}}
                            </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-gray-800">
                                {{$record->roles->pluck('name')->join(', ')}}
                            </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if (!$record->hasRole('Super Admin'))
                                        <a href="{{route('users.edit', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        @can('Super User')
                                            <span class="text-gray-400 px-1">|</span>
                                            <form action="{{route('users.destroy', $record->id)}}" method="post" class="inline">
                                                @method('delete')
                                                @csrf
                                                <a onclick="this.parentNode.submit()" role="button" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</a>
                                            </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty

                        @endforelse
                        </tbody>
                        @if ($records->hasPages())
                            <tfoot>
                            <tr><td colspan="5" class="px-6 text-left text-xs text-gray-500  tracking-wider">{{ $records->links() }}</td></tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <livewire:invitation-manager />
    </div>

</x-app-layout>
