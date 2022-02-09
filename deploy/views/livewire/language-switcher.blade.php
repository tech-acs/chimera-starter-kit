<div>
    <select wire:change="changeHandler($event.target.value);" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-transparent sm:text-sm rounded-md">
        @foreach($languages as $value => $label)
            <option value="{{$value}}" @if($value === $locale) selected @endif>{{ $label }}</option>
        @endforeach
    </select>
</div>
