<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 space-y-6">

        @forelse($reports ?? [] as $report)

            <x-home.summary>

            </x-home.summary>

        @empty
            <x-simple-card>
                {{ __('There are no reports to display.') }}
            </x-simple-card>

        @endforelse

    </div>

</x-app-layout>
