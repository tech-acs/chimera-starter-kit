<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Areas') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Upload shapefile containing area maps, names and codes') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if (session('message'))
            <div class="rounded-md bg-blue-50 p-4 py-3 my-4 mb-4 border border-blue-300">
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

        <form action="{{route('developer.area.store')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Shapefile') }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ __('Your area shapefiles must include name and code columns.') }}</p>
                            <p class="mt-2 text-sm text-gray-500">{{ __('You need to upload the three required component files of a shapefile. The main, index and attributes files with .shp, .shx and .dbf file extensions respectively.') }}</p>
                        </div>
                        <div class="mt-5 space-y-6 md:col-span-2 md:mt-0">
                            <div>
                                {{--<label class="block text-sm font-medium text-gray-700">Cover photo</label>--}}
                                <div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pt-5 pb-6">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="shapefile" class="relative cursor-pointer rounded-md bg-white font-medium text-indigo-600 focus-within:outline-none  hover:text-indigo-500">
                                                <span>{{ __('Upload a file') }}</span>
                                                <input id="shapefile" name="shapefile[]" type="file" multiple class="sr-only">
                                            </label>
                                            <p class="pl-1">{{ __('or drag and drop') }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500">SHP, SHX & DBF</p>
                                    </div>
                                </div>
                                <x-jet-input-error for="shapefile" />
                            </div>
                        </div>

                        <div class="md:col-span-1 pt-4 md:pt-0">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Level') }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ __('This says what area hierarchy is present in the given shapefile.') }}</p>
                        </div>
                        <div class="mt-5 md:col-span-2 md:mt-0">
                            <select id="level" name="level" class="mt-1 w-1/3 rounded-md border border-gray-300 bg-white px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('Select level') }}</option>
                                @forelse($levels as $level => $name)
                                    <option value="{{ $level }}" @selected(old('level') === $level)>{{ __($name) }}</option>
                                @empty
                                    <option value="">{{ __('Not configured') }}</option>
                                @endforelse
                            </select>
                            <x-jet-input-error for="level" />
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <x-jet-button>
                        {{ __('Upload') }}
                    </x-jet-button>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
