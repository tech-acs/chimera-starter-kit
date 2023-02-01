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
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Spreadsheet') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('Your spreadsheet must be formatted in such a way that it includes all your areas') }}</p>
            </div>
            <div class="mt-5 md:col-span-2 md:mt-0">
                <div>
                    <div class="flex flex-grow items-center">
                        <label for="spreadsheet" class="flex justify-between w-2/3 rounded-md sm:text-sm border border-gray-300">
                            <span wire:loading.remove wire:target="spreadsheet" id="file_label" class="my-auto pl-4 text-gray-700">{{ $spreadsheet?->getClientOriginalName() ?? "Choose your file" }}</span>
                            <span wire:loading wire:target="spreadsheet" class="my-auto pl-4 text-gray-700">Uploading...</span>
                            <div class="relative inline-flex items-center hover:bg-gray-100 cursor-pointer space-x-2 px-4 py-2 border-0 border-l rounded-r-md border-gray-300 text-sm font-medium text-gray-700 bg-gray-50">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path></svg>
                                <span>Browse</span>
                            </div>
                        </label>
                        <input type="file" id="spreadsheet" name="spreadsheet" wire:model="spreadsheet" onchange="document.getElementById('file_label').innerText=this.files[0].name;" class="hidden">
                        @if ($fileAccepted)
                            <x-chimera::icon.accepted />
                        @endif
                        @if($errors->has('spreadsheet'))
                            <x-chimera::icon.rejected />
                        @endif
                    </div>
                </div>
                @if($errors->has('spreadsheet'))
                    <x-jet-input-error for="spreadsheet" />
                @else
                    <div class="text-xs text-gray-500 mt-1">
                        You must upload a spreadsheet (.csv)
                    </div>
                @endif
            </div>

            <div class="md:col-span-1 pt-4 md:pt-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Column mapping') }}</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Please map the columns of your file to the right area hierarchies.
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    You can specify the desired (including the padded 0s) length of your
                    code columns in the 'Zero pad code to length' column.
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    Leaving it with the default value of 0 means no padding will occur.
                </p>
            </div>
            <div class="mt-5 md:col-span-2 md:mt-0">
                <div class="">

                    <table class="min-w-full">
                        <thead>
                        <tr>
                            <th scope="col" class="py-2 pl-4 pr-1 text-left text-sm font-semibold text-gray-900 sm:pl-6 md:pl-0"></th>
                            <th scope="col" class="py-2 px-3 text-left text-sm font-semibold text-gray-900">{{ __('Name') }}</th>
                            <th scope="col" class="py-2 px-3 text-left text-sm font-semibold text-gray-900">{{ __('Code') }}</th>
                            <th scope="col" class="py-2 px-3 text-left text-sm font-semibold text-gray-900 w-32 text-center">{{ __('Zero pad code to length') }}</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        @forelse($areaLevels as $areaLevel)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-1 text-sm font-medium text-gray-900 sm:pl-6 md:pl-0">{{ str()->ucfirst($areaLevel) }}</td>
                                <td class="align-top whitespace-nowrap py-4 px-3 text-sm text-gray-500">
                                    <select wire:model="columnMapping.{{ $areaLevel }}.name" class="w-full rounded-md border border-gray-300 bg-white px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                        <option value="">{{ __('Select column') }}</option>
                                        @foreach($columnHeaders as $column)
                                            <option value="{{ $column }}">{{ $column }}</option>
                                        @endforeach
                                    </select>
                                    <x-jet-input-error for="columnMapping.{{ $areaLevel }}.name" class="text-xs" />
                                </td>
                                <td class="align-top whitespace-nowrap py-4 px-3 text-sm text-gray-500">
                                    <select wire:model="columnMapping.{{ $areaLevel }}.code" class="w-full rounded-md border border-gray-300 bg-white px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                        <option value="">{{ __('Select column') }}</option>
                                        @foreach($columnHeaders as $column)
                                            <option value="{{ $column }}">{{ $column }}</option>
                                        @endforeach
                                    </select>
                                    <x-jet-input-error for="columnMapping.{{ $areaLevel }}.code" class="text-xs" />
                                </td>
                                <td class="align-top whitespace-nowrap py-4 px-3 text-sm text-gray-500 align-top">
                                    <input wire:model="columnMapping.{{ $areaLevel }}.zeroPadding" type="number" class="w-full rounded-md border border-gray-300 bg-white px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                    {{--<x-jet-input-error for="columnMapping.{{ $areaLevel }}.zeroPadding" class="text-xs" />--}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">You have not configured your area hierarchies.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <a href="{{ route('developer.area.index') }}"><x-jet-secondary-button class="mr-2">{{ __('Cancel') }}</x-jet-secondary-button></a>
        <x-jet-button wire:click.prevent="import()">
            {{ __('Import') }}
        </x-jet-button>
    </div>
</div>

