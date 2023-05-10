<div class="shadow sm:rounded-md sm:overflow-hidden">

    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">

        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-start">
                <span class="pr-3 bg-white text-sm uppercase tracking-wide text-gray-500">{{ __('Source') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 pl-3 md:pl-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-jet-label for="name" value="{{ __('Name') }} *" />
                    <x-jet-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{old('name', $questionnaire->name ?? null)}}" />
                    <x-jet-input-error for="name" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="title" value="{{ __('Display title') }} *" />
                    <x-chimera::multi-lang-input id="title" name="title" value="{{old('title', $questionnaire->title ?? null)}}" />
                    <x-jet-input-error for="title" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <x-jet-label for="start_date" value="{!! __('Exercise start date') !!} *" />
                    <x-jet-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{old('start_date', optional($questionnaire ?? null)->start_date?->format('Y-m-d') ?? null)}}" />
                    <x-jet-input-error for="start_date" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="end_date" value="{!! __('Exercise end date') !!} *" />
                    <x-jet-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{old('end_date', optional($questionnaire ?? null)->end_date?->format('Y-m-d') ?? null)}}" />
                    <x-jet-input-error for="end_date" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="case_stats_component" value="{!! __('Case stats component') !!}" class="inline" />
                    <a title="Overriding livewire components need to have 'CaseStats' in their names">
                        <svg class="inline w-5 h-5 text-blue-700 cursor-pointer" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"></path>
                        </svg>
                    </a>
                    <select name="case_stats_component" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        @foreach($components ?? [] as $slug => $component)
                            <option value="{{ $slug }}" @selected(old('case_stats_component', $questionnaire->case_stats_component ?? 'case-stats') == $slug)>{{ $component }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <x-jet-label for="show_on_home_page" value="{!! __('Show on home page') !!}" />
                    <select name="show_on_home_page" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="1" @selected(old('show_on_home_page', $questionnaire->show_on_home_page ?? false) == true)>{{ __('Yes') }}</option>
                        <option value="0" @selected(old('show_on_home_page', $questionnaire->show_on_home_page ?? false) == false)>{{ __('No') }}</option>
                    </select>
                </div>
                <div>
                    <x-jet-label for="rank" value="{!! __('Rank (home page listing order)') !!}" />
                    <x-jet-input name="rank" type="number" class="w-20" value="{{ old('rank', $questionnaire->rank ?? null) }}" />
                </div>
            </div>
        </div>

        <div class="relative mt-4">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-start">
                <span class="pr-3 bg-white text-sm uppercase tracking-wide text-gray-500">{{ __('Connection') }} </span>
            </div>
        </div>

        <div class="pl-3 md:pl-6 space-y-6">
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <x-jet-label for="active" value="{{ __('Database driver') }} *" />
                    <select name="driver" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($databases as $name => $driver)
                        <option value="{{$driver}}" @selected(old('driver', $questionnaire->driver ?? false) == $driver)>{{ $name }}</option>
                    @endforeach
                    </select>
                </div>
                <div>
                    <x-jet-label for="host" value="{{ __('Host') }} *" />
                    <x-jet-input id="host" name="host" type="text" class="mt-1 block w-full" value="{{old('host', $questionnaire->host ?? null)}}" />
                    <x-jet-input-error for="host" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="port" value="{{ __('Port') }} *" />
                    <x-jet-input id="port" name="port" type="text" class="mt-1 block w-full" value="{{old('port', $questionnaire->port ?? null)}}" />
                    <x-jet-input-error for="port" class="mt-2" />
                </div>
            </div>
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <x-jet-label for="database" value="{{ __('Database') }} *" />
                    <x-jet-input id="database" name="database" type="text" class="mt-1 block w-full" value="{{old('database', $questionnaire->database ?? null)}}" />
                    <x-jet-input-error for="database" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="username" value="{!! __('Username') !!} *" />
                    <x-jet-input id="username" name="username" type="text" class="mt-1 block w-full" value="{{old('username', $questionnaire->username ?? null)}}" />
                    <x-jet-input-error for="username" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="password" value="{{ __('Password') }} *" />
                    <div class="relative mt-1 rounded-md shadow-sm" x-data="{eyeOpener: true}" x-cloak>
                        <input id="password" name="password" x-bind:type="eyeOpener ? 'password' : 'text'" type="password" value="{{old('password', $questionnaire->password ?? null)}}" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block w-full">
                        <div class="absolute inset-y-0 right-0 flex items-center cursor-pointer">
                            <div class="mr-2 text-gray-500" x-show="eyeOpener" x-on:click="eyeOpener = false" title="{{ __('Show password') }}">
                                {{-- eye --}}
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                            </div>
                            <div class="mr-2 text-gray-500" x-show="! eyeOpener" x-on:click="eyeOpener = true" title="{{ __('Hide password') }}">
                                {{-- eye-off --}}
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <x-jet-input-error for="password" class="mt-2" />
                </div>
            </div>
            <div>
                <x-jet-label for="active" value="{{ __('Active') }}" />
                <select name="connection_active" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="1" @selected(old('connection_active', $questionnaire->connection_active ?? false) == true)>{{ __('Yes') }}</option>
                    <option value="0" @selected(old('connection_active', $questionnaire->connection_active ?? false) == false)>{{ __('No') }}</option>
                </select>
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-secondary-button class="mr-2"><a href="{{ route('developer.questionnaire.index') }}">{{ __('Cancel') }}</a></x-jet-secondary-button>
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
