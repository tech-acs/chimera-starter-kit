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

        <div>
            <div class="mx-auto max-w-7xl">
                <div class="mt-2 grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:grid-rows-2 sm:gap-x-4 lg:gap-8">
                    @foreach($graphicalMenu as $menu)
                        <x-chimera::graphical-menu title="{{ $menu['title'] }}" description="{{ $menu['description'] }}" link="{{ $menu['link'] }}" image="{{ $menu['image'] }}" />
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
