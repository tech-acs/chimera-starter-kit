<div class="shadow sm:rounded-md sm:overflow-hidden mt-4">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        @if ($message)
            <div class="rounded-md bg-blue-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Heroicon name: mini/information-circle -->
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M19 10.5a8.5 8.5 0 11-17 0 8.5 8.5 0 0117 0zM8.25 9.75A.75.75 0 019 9h.253a1.75 1.75 0 011.709 2.13l-.46 2.066a.25.25 0 00.245.304H11a.75.75 0 010 1.5h-.253a1.75 1.75 0 01-1.709-2.13l.46-2.066a.25.25 0 00-.245-.304H9a.75.75 0 01-.75-.75zM10 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-blue-700">{{ $message }}</p>
                        <p class="mt-3 text-sm md:mt-0 md:ml-6"></p>
                    </div>
                </div>
            </div>
        @endif
        <div>
            <div class="mt-5 md:col-span-2 md:mt-0">
                <div>
                    @if ($areaHierarchies->isEmpty())
                        You have not configured your area hierarchies yet.
                    @else
                    <table class="min-w-full">
                        <thead>
                        <tr>
                            <th scope="col" class="py-2 pl-4 pr-1 text-left text-sm font-semibold text-gray-900 sm:pl-6 md:pl-0"></th>
                            <th scope="col" class="py-2 px-3 text-left text-sm font-semibold text-gray-900">{{ __('Where column') }}</th>
                            <th scope="col" class="py-2 px-3 text-left text-sm font-semibold text-gray-900">{{ __('Select column') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($areaHierarchies as $index => $areaHierarchy)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-1 text-sm font-medium text-gray-900 sm:pl-6 md:pl-0">{{ str()->ucfirst($areaHierarchy->name) }}</td>
                                <td class="align-top whitespace-nowrap py-4 px-3 text-sm text-gray-500">
                                    <x-jet-input type="text" wire:model="areaHierarchies.{{ $index }}.where_column" class="w-full" />
                                    <x-jet-input-error for="areaHierarchies.{{ $index }}.where_column" class="text-xs" />
                                </td>
                                <td class="align-top whitespace-nowrap py-4 px-3 text-sm text-gray-500">
                                    <x-jet-input type="text" wire:model="areaHierarchies.{{ $index }}.select_column" class="w-full" />
                                    <x-jet-input-error for="areaHierarchies.{{ $index }}.select_column" class="text-xs" />
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end items-center">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>
        <a href="{{ route('developer.questionnaire.index') }}"><x-jet-secondary-button class="mr-2">{{ __('Cancel') }}</x-jet-secondary-button></a>
        <x-jet-button wire:click.prevent="save()">
            {{ __('Save') }}
        </x-jet-button>
    </div>
</div>
