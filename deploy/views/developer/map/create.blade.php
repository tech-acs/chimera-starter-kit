<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Areas') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Import hierarchical data from files containing area maps, names and codes') }}
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

        <div x-cloak x-data="{
            selectedId: null,
            init() {
                // Set the first tab on page load.
                this.$nextTick(() => this.select('shapefile'))
            },
            select(id) {
                this.selectedId = id
            },
            isSelected(id) {
                return this.selectedId === id
            }
        }"
        >
            {{--Tabs--}}
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a
                            @click="select('shapefile')"
                            :class="isSelected('shapefile') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Shapefile
                            <svg class="w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><polyline points="96 184 32 200 32 56 96 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline><polygon points="160 216 96 184 96 40 160 72 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polygon><polyline points="160 72 224 56 224 200 160 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></polyline></svg>
                        </a>

                        <a
                            @click="select('spreadsheet')"
                            :class="isSelected('spreadsheet') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Spreadsheet
                            <svg class="w-5 h-5 ml-2" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M32,56H224a0,0,0,0,1,0,0V192a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V56A0,0,0,0,1,32,56Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></path><line x1="32" y1="104" x2="224" y2="104" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><line x1="32" y1="152" x2="224" y2="152" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line><line x1="88" y1="104" x2="88" y2="200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"></line></svg>
                        </a>
                    </nav>
                </div>
            </div>
            {{--Content--}}
            <div>
                {{-- First tab --}}
                <section id="users" x-show="isSelected('shapefile')">
                    <form action="{{route('developer.area.store')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="shadow sm:rounded-md sm:overflow-hidden mt-4">
                            <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                                <div class="md:grid md:grid-cols-3 md:gap-6">
                                    <div class="md:col-span-1">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Shapefile') }}</h3>
                                        <p class="mt-2 text-sm text-gray-500">{{ __('Your shapefile must have name and code columns.') }}</p>
                                    </div>
                                    <div class="mt-5 md:col-span-2 md:mt-0">
                                        <div>
                                            <div class="flex items-stretch flex-grow">
                                                <label for="shapefile" class="flex justify-between w-2/3 rounded-md sm:text-sm border border-gray-300">
                                                    <span id="shapefile_label" class="my-auto pl-4 text-gray-700">Choose your files</span>
                                                    <div class="relative inline-flex items-center hover:bg-gray-100 cursor-pointer space-x-2 px-4 py-2 border-0 border-l rounded-r-md border-gray-300 text-sm font-medium text-gray-700 bg-gray-50">
                                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path></svg>
                                                        <span>Browse</span>
                                                    </div>
                                                </label>
                                                <input
                                                    multiple
                                                    type="file"
                                                    id="shapefile"
                                                    class="hidden"
                                                    name="shapefile[]"
                                                    onchange="document.getElementById('shapefile_label').innerText = Array.from(this.files).map(f => f.name).join(', ')"
                                                >
                                            </div>
                                        </div>
                                        @if($errors->has('shapefile'))
                                            <x-jet-input-error for="shapefile" />
                                        @else
                                            <div class="text-xs text-gray-500 mt-1">
                                                You must upload three files that make up the shapefile (.shp, .shx and .dbf)
                                            </div>
                                        @endif
                                    </div>

                                    <div class="md:col-span-1 pt-4 md:pt-0">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Level') }}</h3>
                                        <p class="mt-2 text-sm text-gray-500">{{ __('This says what area hierarchy is present in the given shapefile.') }}</p>
                                    </div>
                                    <div class="mt-5 md:col-span-2 md:mt-0">
                                        <select id="level" name="level" class="mt-1 w-1/3 rounded-md border border-gray-300 bg-white px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                            <option value="">{{ __('Select level') }}</option>
                                            @forelse($levels as $level => $name)
                                                <option @if(old('level') === $level) selected @endif value="{{ $level }}" @selected(old('level') === $level) >{{ __($name) }}</option>
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
                                    {{ __('Import') }}
                                </x-jet-button>
                            </div>

                        </div>
                    </form>
                </section>
                {{-- Second tab --}}
                <section id="invitations" x-show="isSelected('spreadsheet')">
                    <livewire:area-spreadsheet-importer />
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
