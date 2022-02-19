<div class="shadow sm:rounded-md sm:overflow-hidden">
    {{--<div class="px-4 py-2 sm:px-6 bg-gray-50 border-b border-gray-200">
        <span class="text-xs text-gray-500 uppercase">
            Create a new database connection
        </span>
    </div>--}}
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div class="">
                <x-jet-label for="title" value="{{ __('Title') }}" />
                <x-jet-input id="title" name="title" type="text" class="mt-1 block w-full" value="{{old('title', $page->title ?? null)}}" />
                <x-jet-input-error for="title" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="description" value="{{ __('Description') }}" />
                <textarea name="description" rows="5" class='w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'>{{old('description', $page->description ?? null)}}</textarea>
                <x-jet-input-error for="description" class="mt-2" />
            </div>
            <div class="">
                <x-jet-label for="connection" value="{{ __('Connection') }}" />
                <select name="connection" class="mt-1 block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    @foreach($connections as $connection)
                        <option value="{{ $connection }}" {{old('connection', $connection ?? null) ? 'selected' : ''}}>{{ $connection }}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="connection" class="mt-2" />
            </div>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-button>
            {{ __('Submit') }}
        </x-jet-button>
    </div>
</div>
