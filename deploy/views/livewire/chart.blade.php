@once
    @push('scripts')
        <script src="{{ asset('js/plotly-basic.js', config('chimera.secure'))}}"></script>
    @endpush
@endonce

@push('late-scripts')
    <script defer>
        Plotly.newPlot('{!! $graphDiv !!}', {!! $data !!}, {!! $layout !!}, {!! $config !!});

        Livewire.on("redrawChart-{!! $graphDiv !!}", (data, layout) => {
            let newData = JSON.parse(data)
            let newLayout = JSON.parse(layout)
            Plotly.react("{!! $graphDiv !!}", newData, newLayout)
        });

        /*/!*document.getElementById('{!! $graphDiv !!}').on('plotly_click', function(data){
            let payload = _.pick(data.points[0], ['x', 'y', 'pointIndex', 'pointNumber']); // 0 here might grow per trace???
            Livewire.emit('chartClicked', payload);
        });*!/*/
    </script>
@endpush

<div class="relative z-0 px-4 py-5 sm:px-6">
    {{--<a title="Updated: {{$dataTimestamp}}"><svg class="w-5 h-5 inline absolute inset-0 ml-5 mt-4 z-50 text-gray-200 cursor-pointer" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg></a>--}}
    <div id="{{$graphDiv}}" wire:ignore></div>

    <div wire:loading.flex class="absolute inset-0 justify-center items-center z-10 opacity-80 bg-white">
        {{ __('Updating...') }}
        <svg class="animate-spin h-5 w-5 mr-3 ..." viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="gray" stroke-width="4"></circle>
            <path class="opacity-75"  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

</div>
