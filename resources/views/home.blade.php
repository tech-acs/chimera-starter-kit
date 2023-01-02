<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">
        @forelse($questionnaires as $questionnaire)
            <x-chimera-summary :questionnaire="$questionnaire">
                @forelse($questionnaire->scorecards as $scorecard)
                    @livewire('scorecard.' . $scorecard->slug, ['scorecard' => $scorecard, 'index' => $loop->index])
                @empty
                    {{ __('There are no scorecards to display.') }}
                @endforelse
            </x-chimera-summary>
        @empty
            <x-chimera-simple-card>
                {{ __('There are no questionnaires to display.') }}
            </x-chimera-simple-card>
        @endforelse
    </div>

</x-app-layout>
