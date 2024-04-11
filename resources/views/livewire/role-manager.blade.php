<div class="bg-white shadow sm:rounded-lg sm:overflow-hidden max-w-7xl mx-auto">
    <div class="divide-y divide-gray-200">
        <div class="px-4 py-3 sm:px-6 bg-gray-50">
            <h2 id="notes-title" class="text-base font-medium text-gray-800">{{$role->name}}</h2>
        </div>
        <div class="px-4 py-6 sm:px-6">
            @foreach(($permissionGroups ?? []) as $permissionGroup)
                <div class="bg-white sm:rounded-lg border border-gray-200 mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <div class="-ml-4 -mt-4 flex justify-between items-center flex-wrap sm:flex-nowrap">
                            <div class="ml-4 mt-4">
                                <h3 class="text-base leading-6 font-medium text-gray-900">
                                    {{$permissionGroup['title']}}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{$permissionGroup['description']}}
                                </p>
                            </div>
                            <div class="ml-4 mt-4 flex-shrink-0">

                                <div class="flex items-center" x-data="{ on: '{{$permissions[$permissionGroup['permission_name']]}}' }" wire:model="permissions.{{$permissionGroup['permission_name']}}">
                                    <span class="mr-3 text-sm font-medium text-gray-900">Show page</span>
                                    <button type="button" @click="on = !on; $dispatch('input', on)" :class="{ 'bg-indigo-600' : on, 'bg-gray-200' : !on }" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" role="switch">
                                        <span aria-hidden="true" :class="{ 'translate-x-5' : on, 'translate-x-0' : !on }" class="translate-x-0 pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:px-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($permissionGroup['permissionables'] as $permissionable)
                            <div class="bg-white px-4 py-5 sm:px-6 overflow-hidden border border-gray-200 rounded">
                                <div class="flex items-center justify-between gap-4">
                                <span class="flex-grow flex flex-col gap-1">
                                    <span class="text-sm font-medium text-gray-900">{{$permissionable['title']}}</span>
                                    <span class="text-sm text-gray-500">{{$permissionable['description']}}</span>
                                </span>

                                    <div class="flex items-center" x-data="{ on: '{{$permissions[$permissionable['permission_name']]}}' }" wire:model="permissions.{{$permissionable['permission_name']}}">
                                        <span class="mr-3 text-sm font-medium text-gray-900">{{$text ?? ''}}</span>
                                        <button type="button" @click="on = !on; $dispatch('input', on)" :class="{ 'bg-indigo-600' : on, 'bg-gray-200' : !on }" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" role="switch">
                                            <span aria-hidden="true" :class="{ 'translate-x-5' : on, 'translate-x-0' : !on }" class="translate-x-0 pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <span class="block bg-gray-50 text-sm font-medium text-gray-500 text-center px-4 py-2 sm:rounded-b-lg">{{$permissionGroup['count']}} permission {{str('item')->plural($permissionGroup['count'])}} on this page</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="bg-gray-50 px-4 py-4 sm:px-6">
        <div class="flex space-x-3 justify-end items-center">
            <x-action-message class="mr-3" on="roleUpdated">
                {{ __('Saved.') }}
            </x-action-message>
            <x-button wire:click="save" wire:loading.attr="disabled">
                {{ __('Save') }}
            </x-button>
        </div>
    </div>
</div>

