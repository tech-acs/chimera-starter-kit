<x-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Assigned Area') }}
    </x-slot>

    <x-slot name="description">
        {{ __('This is the area you have been assigned to. All data in your dashboard is scoped to this.') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4 text-lg">
            {{ auth()->user()->areaRestrictionAsString() }}
        </div>
    </x-slot>
</x-form-section>
