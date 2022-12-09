<div class="shadow sm:rounded-md sm:overflow-hidden">
    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-jet-label for="name" value="{{ __('Name') }}" />
                <x-chimera::multi-lang-input id="name" name="name" type="text" value="{{old('name', $area->name ?? null)}}" />
                <x-jet-input-error for="name" class="mt-2" />
            </div>
            {{--<div class="">
                <x-jet-label for="code" value="{{ __('Code') }}" />
                <x-jet-input id="code" name="code" type="text" value="{{old('code', $area->code ?? null)}}" />
                <x-jet-input-error for="code" class="mt-2" />
            </div>--}}
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <x-jet-secondary-button onclick="window.history.back();" class="mr-2">{{ __('Cancel') }}</x-jet-secondary-button>
        <x-jet-button>{{ __('Submit') }}</x-jet-button>
    </div>
</div>
