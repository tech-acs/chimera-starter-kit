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
        <div class="text-right">
            {{--<a href="{{route('page.create')}}"><x-button>{{ __('Create new') }}</x-button></a>--}}
        </div>
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
        @if ($errors->any())
            <div class="rounded-md p-4 py-3 mt-4 mb-4 border bg-red-100 border-red-400">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Heroicon name: solid/information-circle -->
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-3 flex-1 md:flex md:justify-between text-sm text-red-700">
                        <ul class="">
                            @foreach($errors->all() as $error)
                                <li class="">{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden border border-gray-200 sm:rounded-lg p-4">
                        <div x-cloak x-data="{
                            selectedId: '{{ session('tab', 'ownership') }}',

                            tabTo(id) {
                                this.selectedId = id
                            },
                            isSelected(id) {
                                return this.selectedId === id
                            }
                        }">
                            <div class="sm:hidden">
                                <label for="tabs" class="sr-only">Select a tab</label>
                                <!-- Use an "onChange" listener to redirect the user to the selected tab URL. -->
                                <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option>My Account</option>
                                    <option>Company</option>
                                    <option selected>Team Members</option>
                                    <option>Billing</option>
                                </select>
                            </div>

                            <div class="hidden sm:block">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        <a
                                            @click="tabTo('ownership')"
                                            :class="isSelected('ownership') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                            class="cursor-pointer group inline-flex items-center border-b-2 py-4 px-1 text-sm font-medium"
                                        >
                                            <svg class="-ml-0.5 mr-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z" />
                                            </svg>
                                            <span :class="isSelected('ownership') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500'">App Ownership</span>
                                        </a>

                                        <a
                                            @click="tabTo('color_palettes')"
                                            :class="isSelected('color_palettes') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                            class="cursor-pointer group inline-flex items-center border-b-2 py-4 px-1 text-sm font-medium"
                                        >
                                            <svg class="-ml-0.5 mr-2 h-5 w-5" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21a9 9 0 0 1 0 -18c4.97 0 9 3.582 9 8c0 1.06 -.474 2.078 -1.318 2.828c-.844 .75 -1.989 1.172 -3.182 1.172h-2.5a2 2 0 0 0 -1 3.75a1.3 1.3 0 0 1 -1 2.25" /><path d="M8.5 10.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M16.5 10.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                            </svg>
                                            <span :class="isSelected('color_palettes') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500'">Color Palettes</span>
                                        </a>

                                    </nav>
                                </div>
                            </div>

                            <section id="ownership" x-show="isSelected('ownership')" class="p-4 bg-white">

                                <div class="py-4">
                                    <h2 class="font-bold tracking-tight text-gray-900 text-2xl">App owner details</h2>
                                    <p class="mt-2 text-lg leading-8 text-gray-600">
                                        You can set the application owner name and website here. These will be reflected in the site wide footer.
                                    </p>
                                </div>

                                <article class="rounded-md border border-gray-400 overflow-hidden bg-white">
                                    <form method="post" action="{{ route('setting.update') }}">
                                        @csrf
                                        <input type="hidden" name="tab" value="ownership">
                                        <div class="p-6">
                                            <div class="grid grid-cols-1 gap-6">
                                                @foreach($settings as $setting)
                                                    <div class="mt-1">
                                                        <x-label for="{{ $setting->key }}" value="{{ $setting->label }}" />
                                                        <x-input id="{{ $setting->key }}" name="{{ $setting->key }}" type="text" class="mt-1 block w-3/4" value="{{ $setting->value }}" />
                                                        {{--<x-input-error for="name" class="mt-2" />--}}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                            <x-button type="submit">{{ __('Save') }}</x-button>
                                        </div>
                                    </form>
                                </article>

                            </section>

                            <section id="color_palettes" x-show="isSelected('color_palettes')" class="p-4 bg-white">
                                <div class="py-4">
                                    <h2 class="font-bold tracking-tight text-gray-900 text-2xl">Color palette selector</h2>
                                    <p class="mt-2 mb-4 text-lg leading-8 text-gray-600">
                                        You can apply one of the following available color palettes. The colors in the selected palette will apply to dashboard elements
                                        such as charts, scorecards and cards. The right color will be chosen for text sitting on top of these colors according to Web Content Accessibility Guidelines (WCAG-3/APCA).
                                    </p>

                                    <div class="bg-emerald-50 py-2 my-4 mb-10 sm:py-4 border rounded-md">
                                        <div class="mx-auto max-w-7xl px-6 lg:px-8">
                                            <div class="mx-auto max-w-4xl lg:text-center">
                                                <p class="mt-2 text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">A brief primer on data visualization colors</p>
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
                                            <div class="">
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
                                                @if (settings('color_palette') === $palette->name)
                                                    <span class="uppercase font-semibold text-gray-400">
                                                        <svg class="w-5 h-5 inline -mt-1 mr-1" fill="none" stroke-width="3" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
</svg>
                                                        Selected</span>
                                                @else
                                                    <form method="post" action="{{ route('setting.update') }}">
                                                        @csrf
                                                        <input type="hidden" name="tab" value="color_palettes">
                                                        <input type="hidden" name="color_palette" value="{{ $palette->name }}">
                                                        <x-secondary-button type="submit">Select</x-secondary-button>
                                                    </form>
                                                @endif

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
                            </section>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
