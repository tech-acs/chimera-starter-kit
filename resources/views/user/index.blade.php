<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('User management') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">{{ __('Users can sign-up when they receive their unique registration link (invite).') }}
            {{ __('You can then assign users one of the roles you have setup which will then dictate which features they will have access to.') }}</p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div x-cloak x-data="{
                selectedId: null,
                init() {
                    // Set the first available tab on the page on page load.
                    this.$nextTick(() => this.select('users'))
                },
                select(id) {
                    this.selectedId = id
                },
                isSelected(id) {
                    return this.selectedId === id
                }
            }"
        >
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a
                            @click="select('users')"
                            :class="isSelected('users') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Users
                            <span
                                :class="isSelected('users') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'"
                                class="bg-gray-200 text-gray-900 hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block"
                            >{{ $users_count }}</span>
                        </a>

                        <a
                            @click="select('invitations')"
                            :class="isSelected('invitations') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Invitations
                            <span
                                :class="isSelected('invitations') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'"
                                class="bg-gray-200 text-gray-900 hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block"
                            >{{ $invitations_count }}</span>
                        </a>
                    </nav>
                </div>
            </div>
            <div>
                <section id="users" x-show="isSelected('users')">
                    <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <form class="mb-4 flex justify-end" method="get" action="{{ route('user.index') }}">
                                <div>
                                    <x-jet-input name="search" type="search" class="mt-1 block" placeholder="Search users" value="{{request('search')}}" />
                                </div>
                            </form>
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
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Name') }}
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
                                                <div class="text-sm">
                                                    {{$record->created_at->locale(app()->getLocale())->isoFormat('ll')}}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-gray-800">
                                                    {{$record->roles->pluck('name')->join(', ')}}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if (!$record->hasRole('Super Admin'))
                                                    @if($record->is_suspended)
                                                        <a href="{{route('user.suspension', $record->id)}}" class="text-yellow-600 hover:text-yellow-900" title="Resume (allow) use of the account">{{ __('Resume') }}</a>
                                                    @else
                                                        <a href="{{route('user.suspension', $record->id)}}" class="text-yellow-600 hover:text-yellow-900" title="Pause (stop) use of the account">{{ __('Pause') }}</a>
                                                    @endif
                                                    <span class="text-gray-400 px-1">|</span>
                                                    <a href="{{route('user.edit', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-sm font-medium text-gray-900 text-center p-4">
                                            {{ __('There are no records to display') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    @if ($records->hasPages())
                                        <tfoot>
                                        <tr><td colspan="5" class="px-6 text-left text-xs text-gray-500  tracking-wider">{{ $records->withQueryString()->links() }}</td></tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
                <section id="invitations" x-show="isSelected('invitations')">
                    <livewire:invitation-manager />
                </section>
            </div>
        </div>

    </div>

</x-app-layout>
