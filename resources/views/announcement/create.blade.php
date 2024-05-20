<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Announcements') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Send broadcast messages to all users') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::error-display />

        <form action="{{route('announcement.store')}}" method="POST">
            @csrf
            <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <x-label for="title" value="{{ __('Title') }} *" />
                            <x-input class="w-1/2" id="title" name="title" type="text" value="{{old('title')}}" />
                            <x-input-error for="title" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="title" value="{{ __('Recipients') }} *" />
                            <select name="recipients" class="mt-1 w-1/3 rounded-md border border-gray-300 bg-white px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('Select recipients') }}</option>
                                @foreach($recipients as $roleId => $recipient)
                                    <option value="{{ $roleId }}" @selected(old('recipients') == $roleId)>{{ __($recipient) }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="recipients" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="body" value="{{ __('Message') }} *" />
                            <x-chimera::textarea name="body" id="body" rows="4">{{old('body')}}</x-chimera::textarea>
                            <x-input-error for="body" class="mt-2" />
                        </div>

                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <x-button>
                        {{ __('Send') }}
                    </x-button>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>
