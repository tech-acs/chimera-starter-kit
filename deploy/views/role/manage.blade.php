<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Role management') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">
            {{ __('You can use the features on this page to manage your user roles. Users are assigned roles and') }}
            {{ __('the roles dictate which charts and features of the dashboard they will have access to.') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <livewire:role-manager :role="$role" />
    </div>

</x-app-layout>
