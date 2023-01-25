<div>
    <x-chimera::toggle-button
        :value="$subscribed"
        name="subscribed-to-{{ $report->id }}"
        wire:model="$subscribed"
        wire:click="toggleSubscription"
    />
</div>
