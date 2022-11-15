<div class="shadow sm:rounded-md sm:overflow-hidden">

    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">

        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-start">
                <span class="pr-3 bg-white text-sm uppercase tracking-wide text-gray-500">{{ __('Questionnaire Details ') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 pl-3 md:pl-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-jet-label for="name" value="{{ __('Dictionary name') }} *" />
                    <x-jet-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{old('name', $questionnaire->name ?? null)}}" />
                    <x-jet-input-error for="name" class="mt-2" />
                </div>
                <div>
                    <x-jet-label for="title" value="{{ __('Display title') }} *" />
                    <x-multi-lang-input id="title" name="title" value="{{old('title', $questionnaire->title ?? null)}}" />
                    <x-jet-input-error for="title" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <x-jet-label for="start_date" value="{{ __('Exercise start date') }} *" />
                    <x-jet-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{old('start_date', optional($questionnaire ?? null)->start_date?->format('Y-m-d') ?? null)}}" />
                    <x-jet-input-error for="start_date" class="mt-2" />
                </div>
                <div class="">
                    <x-jet-label for="end_date" value="{{ __('Exercise end date') }} *" />
                    <x-jet-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{old('end_date', optional($questionnaire ?? null)->end_date?->format('Y-m-d') ?? null)}}" />
                    <x-jet-input-error for="end_date" class="mt-2" />
                </div>
                <div class="">
                    <x-jet-label for="show_on_home_page" value="{{ __('Show on home page') }}" />
                    <select name="show_on_home_page" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="1" @selected(old('show_on_home_page', $questionnaire->show_on_home_page ?? false) == true)>{{ __('Yes') }}</option>
                        <option value="0" @selected(old('show_on_home_page', $questionnaire->show_on_home_page ?? false) == false)>{{ __('No') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="relative pt-4">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-start">
                <span class="pr-3 bg-white text-sm uppercase tracking-wide text-gray-500"> {{ __('Database') }} </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pl-3 md:pl-6">
            <div class="grid grid-cols-2 gap-6">
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
            <div class="">
                <x-jet-label for="database" value="{{ __('Database') }} *" />
                <x-jet-input id="database" name="database" type="text" class="mt-1 block w-full" value="{{old('database', $questionnaire->database ?? null)}}" />
                <x-jet-input-error for="database" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="username" value="{{ __('Username') }} *" />
                <x-jet-input id="username" name="username" type="text" class="mt-1 block w-full" value="{{old('username', $questionnaire->username ?? null)}}" />
                <x-jet-input-error for="username" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="password" value="{{ __('Password') }} *" />
                <x-jet-input id="password" name="password" type="password" class="mt-1 block w-full" value="{{old('password', $questionnaire->password ?? null)}}" />
                <x-jet-input-error for="password" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="active" value="{{ __('Active') }}" />
                <select name="connection_active" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="1" @selected(old('connection_active', $questionnaire->connection_active ?? false) == true)>{{ __('Yes') }}</option>
                    <option value="0" @selected(old('connection_active', $questionnaire->connection_active ?? false) == false)>{{ __('No') }}</option>
                </select>
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-secondary-button class="mr-2"><a href="{{ route('questionnaire.index') }}">{{ __('Cancel') }}</a></x-jet-secondary-button>
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
