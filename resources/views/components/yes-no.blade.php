@props(['value' => false, 'trueLabel' => 'Yes', 'falseLabel' => 'No'])
<div {{ $attributes }}>
    @if ($value)
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"> {{ __($trueLabel) }} </span>
    @else
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"> {{ __($falseLabel) }} </span>
    @endif
</div>
