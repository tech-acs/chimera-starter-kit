<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Data sources with their connections') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Editing an existing data source and database connection') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::error-display />

        <div x-cloak x-data="{
                selectedId: null,
                init() {
                    // Set the first available tab on the page on page load.
                    this.$nextTick(() => this.select('basics'))
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
                            @click="select('basics')"
                            :class="isSelected('basics') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Basics
                        </a>
                    </nav>
                </div>
            </div>

            <div class="pt-6">
                <section id="basics" x-show="isSelected('basics')">
                    <form action="{{route('developer.data-source.update', $dataSource->id)}}" method="POST">
                        @csrf
                        @method('PATCH')
                        @include('chimera::developer.data-source.form')
                    </form>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
