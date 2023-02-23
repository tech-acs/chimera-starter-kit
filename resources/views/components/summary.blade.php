<div class="rounded-md bg-white shadow">
    <div class="flex justify-between p-4 border-b border-gray-200 sm:px-6">
        <div>
            <dt>
                <p class="text-2xl font-semibold text-gray-700">{{ $title }} @if (! is_null($area)) ({{ $area->name }}) @endif</p>
            </dt>
            <dd class="flex items-baseline">
                <p class="flex items-baseline text-sm font-semibold">
                    {{$dates['start']->locale(app()->getLocale())->isoFormat('ll')}} - {{$dates['end']->locale(app()->getLocale())->isoFormat('ll')}}
                </p>
            </dd>
        </div>
        <div class="flex flex-col">
            <p class="text-2xl font-semibold text-gray-700 text-right">
                {{ $dates['progress'] }}
            </p>
            <p class="flex items-baseline text-sm font-semibold text-gray-400">
                {{ __('Updated:') }} {{ $lastUpdated }}
            </p>
        </div>
    </div>

    <div class="px-4 sm:px-6 py-4">
        <div>
            <h3 class="text-sm font-semibold uppercase text-left text-gray-600 tracking-wider pb-6">
                {{ __('Interview stats') }}
            </h3>
            @livewire($questionnaire->case_stats_component, ['questionnaire' => $questionnaire])
        </div>

        <div>
            <div class="bg-white">
                <div class="max-w-7xl mx-auto py-6">
                    <p class="text-center text-sm font-semibold uppercase text-gray-600 tracking-wider pb-2">
                        {{ __('A few selected indicators') }}
                    </p>
                    <dl class="rounded-lg bg-white grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {{ $slot }}
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

