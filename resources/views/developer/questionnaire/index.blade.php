<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Sources with their connections') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('All your sources and their database connections should be configured here') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="text-right">
            <a href="{{route('developer.questionnaire.create')}}"><x-jet-button>{{ __('Create new') }}</x-jet-button></a>
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
                            <li class="mb-2 font-semibold">Connection test(s) failed:</li>
                            @foreach($errors->all() as $error)
                                <li class="list-disc">{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Source') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Connection') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($records as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 space-y-1">
                                    <div class="text-sm text-gray-900 font-medium">{{$record->name}} ({{$record->title}})</div>
                                    @if($record->start_date && $record->end_date)
                                        <div class="text-sm text-blue-700">{{$record->start_date->toFormattedDateString()}} - {{$record->end_date->toFormattedDateString()}}</div>
                                    @endif
                                    <div class="text-sm text-gray-500">
                                        {{ __('Show on home page') }}:
                                        <span class="font-semibold">
                                            @if($record->show_on_home_page) {{ __('Yes') }} @else {{ __('No') }} @endif
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-red text-center space-y-1">
                                    <div class="text-sm text-gray-900">
                                        Host: {{$record->host}}
                                        @if ($record->connection_active)
                                            <svg class="w-5 h-5 inline text-green-700" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M237.7,18.3a8.1,8.1,0,0,0-11.4,0L176,68.7l-5.4-5.4a31.9,31.9,0,0,0-45.2,0L100,88.7l-2.3-2.4A8.1,8.1,0,0,0,86.3,97.7l2.4,2.3L63.3,125.4a31.9,31.9,0,0,0,0,45.2l5.4,5.4L18.3,226.3a8.1,8.1,0,0,0,0,11.4,8.2,8.2,0,0,0,11.4,0L80,187.3l5.4,5.4a32.1,32.1,0,0,0,45.2,0L156,167.3l2.3,2.4a8.2,8.2,0,0,0,11.4,0,8.1,8.1,0,0,0,0-11.4l-2.4-2.3,25.4-25.4a31.9,31.9,0,0,0,0-45.2L187.3,80l50.4-50.3A8.1,8.1,0,0,0,237.7,18.3Zm-56.3,101L156,144.7,111.3,100l25.4-25.4a15.9,15.9,0,0,1,22.6,0l22.1,22.1a15.9,15.9,0,0,1,0,22.6Zm50.2,43.2A7.9,7.9,0,0,1,224,168a7.3,7.3,0,0,1-2.5-.4l-24-8a8,8,0,1,1,5-15.2l24,8A8,8,0,0,1,231.6,162.5ZM24.4,93.5a8,8,0,0,1,10.1-5.1l24,8A8,8,0,0,1,56,112a7.3,7.3,0,0,1-2.5-.4l-24-8A8,8,0,0,1,24.4,93.5Zm64-59a8,8,0,0,1,15.2-5l8,24a8,8,0,0,1-5.1,10.1,7.3,7.3,0,0,1-2.5.4,7.9,7.9,0,0,1-7.6-5.5Zm79.2,187a8,8,0,0,1-5.1,10.1,7.3,7.3,0,0,1-2.5.4,7.9,7.9,0,0,1-7.6-5.5l-8-24a8,8,0,1,1,15.2-5Z"></path></svg>
                                        @else
                                            <svg class="w-5 h-5 inline text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"></rect><path d="M237.7,29.7,211.3,56l5.4,5.4a31.9,31.9,0,0,1,0,45.2L191.3,132l2.4,2.3a8.1,8.1,0,0,1,0,11.4,8.2,8.2,0,0,1-11.4,0l-8-8h0l-56-56h0l-8-8a8.1,8.1,0,0,1,11.4-11.4l2.3,2.4,25.4-25.4a31.9,31.9,0,0,1,45.2,0l5.4,5.4,26.3-26.4a8.1,8.1,0,0,1,11.4,11.4ZM138.3,138.3,120,156.7,99.3,136l18.4-18.3a8.1,8.1,0,0,0-11.4-11.4L88,124.7l-6.3-6.4h0l-8-8a8.1,8.1,0,0,0-11.4,11.4l2.4,2.3L39.3,149.4a31.9,31.9,0,0,0,0,45.2l5.4,5.4L18.3,226.3a8.1,8.1,0,0,0,0,11.4,8.2,8.2,0,0,0,11.4,0L56,211.3l5.4,5.4a31.9,31.9,0,0,0,45.2,0L132,191.3l2.3,2.4a8.2,8.2,0,0,0,11.4,0,8.1,8.1,0,0,0,0-11.4l-8-8h0l-6.4-6.3,18.4-18.3a8.1,8.1,0,0,0-11.4-11.4Z"></path></svg>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">{{ __('Database') }}: {{$record->database}} ({{ $record->driver }})</div>
                                    <div class="text-sm text-gray-500">{{ __('Username') }}: {{$record->username}}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($record->connection_active)
                                        <a href="{{route('developer.questionnaire.connection.test', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Test') }}</a>
                                        <span class="text-gray-400 px-1">|</span>
                                    @endif
                                    <a href="{{route('developer.questionnaire.edit', $record->id)}}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                    <span class="text-gray-400 px-1">|</span>
                                    <form action="{{route('developer.questionnaire.destroy', $record->id)}}" method="post" class="inline">
                                        @method('delete')
                                        @csrf
                                        <a onclick="this.parentNode.submit()" role="button" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</a>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                    {{ __('There are no records to display') }}
                                </td>
                            </tr>
                        @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
