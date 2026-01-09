<div class="flex items-center justify-center">
    <div
        x-data="{
            dataStatus: $wire.entangle('dataStatus'),
            displayPercent: 0,
            target: @entangle('value'),
            circumference: 2 * Math.PI * 40,
            animateValue() {
                let startTimestamp = null;
                const duration = 1500;
                const startValue = this.displayPercent;
                const distance = this.target - startValue;

                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);

                    // Ease-out cubic formula
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    this.displayPercent = Math.floor(startValue + (distance * easeOut));

                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }
        }"
        x-init="
            animateValue();
            $watch('target', () => animateValue());
        "
    >
        <div x-show="dataStatus == 'pending'" x-cloak>
            <div wire:poll.visible.3s="checkData"></div>

            @include('chimera::livewire.placeholders.gauge')
        </div>

        <div
            x-cloak
            x-show="dataStatus == 'renderable'"
            x-transition.enter.duration.1000ms
            x-transition.leave.duration.150ms
            class="flex border max-w-96 p-2 py-1 rounded-lg bg-gray-50/30"
        >
            <div class="relative size-24">
                <svg class="size-24 -rotate-90" viewBox="0 0 100 100">
                    <circle class="text-gray-200 stroke-current" stroke-width="13" cx="50" cy="50" r="40" fill="transparent"></circle>
                    <circle
                        class="{{ $scoreColor }} stroke-current"
                        stroke-width="13"
                        stroke-linecap="butt"
                        cx="50" cy="50" r="40"
                        fill="transparent"
                        :stroke-dasharray="circumference"
                        :stroke-dashoffset="circumference - (displayPercent / {{ $outOf }}) * circumference"
                        style="transition: stroke-dashoffset 0.1s linear;"
                    ></circle>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-2xl font-bold text-gray-700">
                    <span x-text="displayPercent"></span><span class="text-xl">{{ $unit }}</span>
                </span>
                </div>
            </div>
            <div class="flex flex-col justify-center px-3">
                <div class="text-xl font-semibold text-gray-600">{{ $title }}</div>
                <div class="text-sm text-gray-500">{{ $subtitle }}</div>
            </div>
        </div>

        <div
            x-show="dataStatus == 'empty'" x-cloak x-transition.duration.500ms
            class="flex min-h-96 justify-center items-center text-4xl text-gray-600 z-60 opacity-90 bg-white px-4 py-5 sm:px-6"
        >
            {{ __('There is no data to display at this area level') }}
        </div>

        <div
            x-show="dataStatus == 'inapplicable'" x-cloak x-transition.duration.500ms
            class="flex border max-w-96 min-h-24 p-2 px-10 items-center text-gray-500 text-wrap rounded-lg bg-gray-50/30"
        >
            {{ __('The current area level is inapplicable to this gauge') }}
        </div>
    </div>
</div>
