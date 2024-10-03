<div>
    <a title="Edit" href="{{ route('scorecard.edit', $row->id) }}" class="text-indigo-600 hover:text-indigo-400">{{ __('Edit') }}</a>
    <span class="text-gray-400 px-1">|</span>
    <livewire:cache-clearer artefact="scorecard" :id="$row->id" />
</div>


