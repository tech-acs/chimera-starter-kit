<div {{ $attributes->merge(['class' => '']) }}>
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            {{$slot}}
        </div>
    </div>
</div>
