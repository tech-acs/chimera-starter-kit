<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('User management') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">
            {{ __('You can use the features on this page to manage your users. Users are assigned roles and
            the roles dictate which charts and features of the dashboard they will have access to.') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg">
            <form class="space-y-6" action="{{route('user.update', $user->id)}}" method="POST">
                @method('patch')
                @csrf
                <div class="md:grid md:grid-cols-3 md:gap-6 px-4 py-5 sm:p-6">
                    <div class="md:col-span-1">
                        {{--<h3 class="text-lg font-medium leading-6 text-gray-900">{{$user->name}}</h3>--}}
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <img class="h-20 w-20 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    {{$user->name}}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{$user->email}}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center text-sm text-gray-500 mt-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-8">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">
                                        {{ __('Registered on') }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{$user->created_at->toDayDateTimeString()}}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-5 md:mt-0 md:col-span-2">
                        <fieldset>
                            <div>
                                <legend class="text-base font-medium text-gray-900">Role</legend>
                                <p class="text-sm text-gray-500">{{ __('You can assign one of the following configured roles to the user') }}</p>
                            </div>
                            <div class="mt-4 space-y-4">
                                <div class="bg-white rounded-md -space-y-px">
                                @forelse($roles as $role)
                                    <!-- Checked: "bg-indigo-50 border-indigo-200 z-10", Not Checked: "border-gray-200" -->
                                    <label class="border-gray-200 relative border p-4 flex cursor-pointer" x-data="{checked: {{$user->hasRole($role->name) ? '1' : '0'}} }">
                                        <input type="radio" name="role" value="{{ $role->name }}" @if($user->hasRole($role->name)) checked @endif class="h-4 w-4 mt-0.5 cursor-pointer text-indigo-600 border-gray-300 focus:ring-indigo-500" aria-labelledby="privacy-setting-0-label" aria-describedby="privacy-setting-0-description">
                                        <div class="ml-3 flex flex-col">
                                            <!-- Checked: "text-indigo-900", Not Checked: "text-gray-900" -->
                                            <span id="privacy-setting-0-label" :class="{'text-indigo-900' : checked == 1, 'text-gray-900': checked == 0 }" class="block text-sm font-medium">
                                                {{$role->name}}
                                            </span>
                                            <!-- Checked: "text-indigo-700", Not Checked: "text-gray-500" -->
                                            <span id="privacy-setting-0-description" :class="{'text-indigo-700' : checked == 1, 'text-gray-500': checked == 0 }" class="text-gray-500 block text-sm">
                                                {{$role->permissions->count()}} permissions
                                            </span>
                                        </div>
                                    </label>
                                @empty
                                    {{ __('There are no roles that have been setup') }}
                                @endforelse
                                </div>
                            </div>
                            <div class="mt-6">
                                <div class="mb-2">
                                    <span class="text-base font-medium text-gray-900">{{ __('Area restriction') }}</span>
                                </div>
                                <livewire:area-restriction-manager :user="$user" />
                            </div>
                        </fieldset>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 sm:rounded-lg">
                    <x-button type="submit">
                        {{ __('Update') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
