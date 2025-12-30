<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Settings') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('You can manage basic, application wide settings here') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />
        <x-chimera::error-display />

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <form method="post" action="{{ route('setting.update') }}" class="bg-gray-50 overflow-hidden border border-gray-200 sm:rounded-lg">
                        @csrf
                        <div class="grid grid-cols-1 gap-x-4 gap-y-8 p-4 md:grid-cols-3"
                             x-cloak x-data="{
                                selectedId: '{{ session('active-tab', 'group1') }}',
                                tabTo(id) {
                                    this.selectedId = id
                                },
                                isSelected(id) {
                                    return this.selectedId === id
                                }
                            }"
                        >
                            {{-- Tabs menu --}}
                            <div class="grid grid-cols-1 items-start content-start gap-y-3">
                                @foreach($groupedSettings as $groupName => $settings)
                                    <div
                                        @click="tabTo('group{{ $loop->iteration }}')"
                                        :class="isSelected('group{{ $loop->iteration }}') ? 'bg-white border border-gray-900/10 sm:rounded-xl' : ''"
                                        class="px-4 py-2 shadow-xs cursor-pointer"
                                    >
                                        <h2 class="text-base/7 font-semibold text-gray-900 dark:text-white">{{ str($groupName)->explode('|')[0] }}</h2>
                                        <p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-400">{{ str($groupName)->explode('|')[1] }}</p>
                                    </div>
                                @endforeach
                                <div
                                    @click="tabTo('group0')"
                                    :class="isSelected('group0') ? 'bg-white border border-gray-900/10 sm:rounded-xl' : ''"
                                    class="px-4 py-2 shadow-xs cursor-pointer"
                                >
                                    <h2 class="text-base/7 font-semibold text-gray-900 dark:text-white">Color palette selector</h2>
                                    <p class="mt-1 text-sm/6 text-gray-600 dark:text-gray-400">Applies sitewide to charts, scorecards and cards.</p>
                                </div>
                                <input type="hidden" name="active-tab" x-model="selectedId">
                            </div>

                            {{-- Tab content --}}
                            @foreach($groupedSettings as $groupName => $settings)
                                <div id="group{{ $loop->iteration }}" x-show="isSelected('group{{ $loop->iteration }}')" class="bg-white shadow-xs border border-gray-900/10 sm:rounded-xl md:col-span-2">
                                    <div class="px-4 py-6 sm:p-8">
                                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                                            @foreach($settings as $setting)
                                                @if($setting->input_type === 'checkbox')
                                                    <div class="col-span-full relative flex items-start">
                                                        <div class="flex h-6 items-center mt-1">
                                                            <input id="{{ $setting->key }}" name="{{ $setting->key }}" @checked($setting->value) type="checkbox" class="h-6 w-6 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                                        </div>
                                                        <div class="ml-3 text-base leading-6">
                                                            <label for="is_featured" class="font-medium text-gray-900">{{ $setting->label }}</label>
                                                            <p class="text-gray-500 text-xs">{{ $setting->help }}</p>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="col-span-full">
                                                        <label for="email" class="block text-sm/6 font-medium text-gray-900 dark:text-white">{{ $setting->label }}</label>
                                                        <div class="mt-2">
                                                            <input id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}"
                                                                   class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block {{ $setting->input_type === 'text' ? 'w-full' : '' }}" />
                                                        </div>
                                                        <p id="email-description" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $setting->help }}</p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    {{--<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8 dark:border-white/10">
                                        <button type="button" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Cancel</button>
                                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Save</button>
                                    </div>--}}
                                </div>
                            @endforeach
                            <div id="group0" x-show="isSelected('group0')" class="bg-white p-6 shadow-xs border border-gray-900/10 sm:rounded-xl md:col-span-2"
                                x-data="{ selectedPalette: '{{ settings('color_palette') }}' }"
                            >
                                <div>
                                    <h3 class="font-bold tracking-tight text-gray-900 text-xl">Color palette selector</h3>
                                    <p class="mb-4 text-base leading-8 text-gray-600">
                                        You can apply one of the following available color palettes. The colors in the selected palette will apply to dashboard elements
                                        such as charts, scorecards and cards. The right color will be chosen for text sitting on top of these colors according to Web Content Accessibility Guidelines (WCAG-3/APCA).
                                    </p>
                                    <div class="bg-emerald-50 py-2 my-4 mb-10 sm:py-4 border rounded-md">
                                        <div class="mx-auto max-w-7xl px-6 lg:px-8">
                                            <div class="mx-auto max-w-4xl lg:text-center">
                                                <p class="mt-2 text-xl font-bold tracking-tight text-gray-900">A brief primer on data visualization colors</p>
                                                <p class="mt-2 text-lg leading-8 text-gray-600">Color improves a chart's aesthetic quality, as well as its ability to effectively communicate about its data.
                                                    The colors used for data visualization can generally be classified into three palettes. Read more <a class="text-blue-500 underline" href="https://spectrum.adobe.com/page/color-for-data-visualization/" target="_blank">here</a>.</p>
                                            </div>
                                            <div class="mx-auto mt-6 max-w-2xl lg:max-w-none">
                                                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-8 lg:max-w-none lg:grid-cols-3">
                                                    <div class="flex flex-col">
                                                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                                                            Categorical
                                                        </dt>
                                                        <dd class="mt-2 flex flex-auto flex-col text-base leading-7 text-gray-600">
                                                            <p class="flex-auto">When you want to present categories that are not correlated, go for a categorical color palette.
                                                                This will make your categories perfectly distinguishable from each other. Categorical colors must be displayed in sequence and contrast with one another.</p>
                                                        </dd>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                                                            Sequential
                                                        </dt>
                                                        <dd class="mt-2 flex flex-auto flex-col text-base leading-7 text-gray-600">
                                                            <p class="flex-auto">Apply a sequential color palette when the variable is numeric or possesses naturally ordered values.
                                                                Being the ideal option for trend charts, sequential palettes will help you highlight the evolution of a certain variable.</p>
                                                        </dd>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900">
                                                            Diverging
                                                        </dt>
                                                        <dd class="mt-2 flex flex-auto flex-col text-base leading-7 text-gray-600">
                                                            <p class="flex-auto">Employ a diverging color palette when the variable is numeric and has a significant center value.
                                                                This is a combination of two sequential palettes that share the same endpoint. As a result, readers will easily distinguish different values falling on either side of the endpoint.</p>
                                                        </dd>
                                                    </div>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @foreach(Uneca\Chimera\Services\ColorPalette::all() as $palette)
                                    <article class="rounded-md border border-gray-400 overflow-hidden bg-gray-100 mb-14">
                                        <header class="border-b border-gray-200 bg-gray-50 p-4 py-3">
                                            <div class="flex flex-wrap items-center justify-between sm:flex-nowrap">
                                                <div>
                                                    <h3 class="text-xl font-semibold leading-6 text-gray-900 inline">{{ $palette->name }}</h3>
                                                    <div class="inline-block ml-4 space-x-1">
                                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/50">
                                                            {{ count($palette->colors) }} colors
                                                        </span>
                                                        @foreach($palette->tags ?? [] as $tag)
                                                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/50">{{ $tag }}</span>
                                                        @endforeach
                                                    </div>
                                                    <p class="mt-1 text-sm text-gray-500">{{ $palette->description }}</p>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <label class="group relative ml-2 flex rounded-lg border border-gray-500 bg-white p-2 has-[:focus-visible]:outline  has-[:focus-visible]:outline-[3px] has-[:focus-visible]:-outline-offset-1"
                                                       :class="selectedPalette == '{{ $palette->name }}' ?
                                                       'outline outline-2 -outline-offset-2 outline-indigo-600' :
                                                       'border-gray-400 bg-gray-200 opacity-50'"
                                                    >
                                                        <input type="radio" name="color_palette" value="{{ $palette->name }}" x-model="selectedPalette" class="hidden" />
                                                        <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" class="size-5 text-indigo-600 dark:text-indigo-500" :class="selectedPalette == '{{ $palette->name }}' ? 'visible mr-1' : 'hidden'">
                                                            <path d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" fill-rule="evenodd" />
                                                        </svg>
                                                        <div class="flex-1">
                                                            <span class="block text-sm font-medium text-gray-900 dark:text-white" x-text="selectedPalette == '{{ $palette->name }}' ? 'Selected' : 'Select'">Select</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </header>
                                        <section class="py-4">
                                            <div class="flex flex-wrap gap-4 px-4">
                                                @foreach($palette->colors as $color)
                                                    <div class="p-1 bg-white border border-gray-200">
                                                        <div class="w-16 h-16" style="background-color: {{ $color }}"></div>
                                                        <span class="text-xs">{{ strtolower($color) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </section>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex -mx-2 items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8 dark:border-white/10">
                            {{--<button type="button" class="text-sm/6 font-semibold text-gray-900 dark:text-white">Cancel</button>--}}
                            <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:shadow-none dark:hover:bg-indigo-400 dark:focus-visible:outline-indigo-500">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
