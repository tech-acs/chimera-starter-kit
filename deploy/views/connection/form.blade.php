<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-2 sm:px-6 bg-gray-50 border-b border-gray-200">
        <span class="text-xs text-gray-500 uppercase">
            Create a new database connection
        </span>
    </div>
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="">
                <x-jet-label for="name" value="{{ __('Connection name') }}" />
                <x-jet-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{old('name', $connection->name ?? null)}}" />
                <x-jet-input-error for="name" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="host" value="{{ __('Host') }}" />
                <x-jet-input id="host" name="host" type="text" class="mt-1 block w-full" value="{{old('host', $connection->host ?? null)}}" />
                <x-jet-input-error for="host" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="port" value="{{ __('Port') }}" />
                <x-jet-input id="port" name="port" type="text" class="mt-1 block w-full" value="{{old('port', $connection->port ?? null)}}" />
                <x-jet-input-error for="port" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="database" value="{{ __('Database') }}" />
                <x-jet-input id="database" name="database" type="text" class="mt-1 block w-full" value="{{old('database', $connection->database ?? null)}}" />
                <x-jet-input-error for="database" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="username" value="{{ __('Username') }}" />
                <x-jet-input id="username" name="username" type="text" class="mt-1 block w-full" value="{{old('username', $connection->username ?? null)}}" />
                <x-jet-input-error for="username" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="password" value="{{ __('Password') }}" />
                <x-jet-input id="password" name="password" type="password" class="mt-1 block w-full" value="{{old('password', $connection->password ?? null)}}" />
                <x-jet-input-error for="password" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="active" value="{{ __('Active') }}" />
                <select name="active" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="1" {{old('active', $connection->active ?? null) ? 'selected' : ''}}>Yes</option>
                    <option value="0" {{old('active', $connection->active ?? null) ? '' : 'selected'}}>No</option>
                </select>
                {{--<x-jet-checkbox name="active" value="1" {{old('active', $connection->active ?? null) ? 'checked' : ''}} />
                <x-jet-label class="inline-block ml-2" for="active" value="{{ __('Active') }}" />
                <x-jet-input-error for="active" class="mt-2" />--}}
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
