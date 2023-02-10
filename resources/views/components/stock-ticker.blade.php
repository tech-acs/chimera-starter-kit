@props(['diff' => 0, 'diffTitle' => null, 'unit' => '%'])
<div title="{{ $diffTitle }}" class="cursor-help inline-flex items-baseline px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800 md:mt-2 lg:mt-0">
    {{ $diff }}{{ $unit }}
</div>
