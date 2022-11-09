<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">

        @forelse($questionnaires as $questionnaire)

            <x-home.summary :questionnaire="$questionnaire">
                @forelse($questionnaire->scorecards as $scorecard)
                    <x-dynamic-component component="home.{{$scorecard->slug}}" :scorecard="$scorecard" :index="$loop->index" />
                @empty
                    {{ __('There are no scorecards to display.') }}
                @endforelse
            </x-home.summary>

        @empty
            <x-simple-card>
                {{ __('There are no questionnaires to display.') }}
            </x-simple-card>

        @endforelse

    </div>

</x-app-layout>
