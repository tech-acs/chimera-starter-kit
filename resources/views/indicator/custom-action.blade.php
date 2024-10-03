<div>
    <a title="Edit" href="{{ route('indicator.edit', $row->id) }}" class="text-indigo-600 hover:text-indigo-400">{{ __('Edit') }}</a>
    @can('developer-mode')
        <span class="text-gray-400 px-1">|</span>
        <a title="Chart designer" href="{{ route('developer.indicator-editor', $row->id) }}" class="text-emerald-600 hover:text-emerald-400">{{ __('Design') }}</a>
    @endcan
    <span class="text-gray-400 px-1">|</span>
    <livewire:indicator-tester :indicator="$row"/>
    <span class="text-gray-400 px-1">|</span>
    <livewire:cache-clearer artefact="indicator" :id="$row->id" />
</div>


