@once

@endonce

@push('late-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var sort_column = -1;
            var sort_order = 'asc';
            var cols = {!! $columns !!} ;

            for (var i = 0; i < cols.length; i++) {
                if (cols[i].sorted !== ""){
                    sort_column = i;
                    sort_order = cols[i].sorted;
                    break;
                }
            }

            var table = $('#{{ preg_replace('/[.-]/', '_', $graphDiv)}}').DataTable( {
                dom: 'Bfirtlp',
                data: {!! $data !!},
                order: [ sort_column, sort_order ],
                buttons: [
                    {
                        extend: 'excel',
                        title: '{!! $title !!}  \n generated on: {!! $currentDate !!}',
                    },
                    {
                        extend: 'pdf',
                        title: '{!! $title !!}  \n generated on: {!! $currentDate !!}',
                        footer: true
                    },
                    {
                        extend: 'print',
                        title: '{!! $title !!}  \n generated on: {!! $currentDate !!}',
                    }

                ],
                columns: {!! $columns !!}
            } );
        }, false);

        Livewire.on("redrawChart-{!! $graphDiv !!}", (d, layout) => {
            var sort_column = -1;
            var sort_order = 'asc';
            var cols = {!! $columns !!} ;

            for (var i = 0; i < cols.length; i++) {
                if (cols[i].sorted !== ""){
                    sort_column = i;
                    sort_order = cols[i].sorted;
                    break;
                }
            }

            data = JSON.parse(d);
            var table = $('#{{ preg_replace('/[.-]/', '_', $graphDiv)}}').DataTable();
            table.clear().destroy();
            table = $('#{{ preg_replace('/[.-]/', '_', $graphDiv)}}').DataTable( {
                dom: 'Bfirtlp',
                data: data,
                order: [ sort_column, sort_order ],
                buttons: [
                    {
                        extend: 'excel',
                        title: '{!! $title !!}  \n generated on: {!! $currentDate !!}',
                    },
                    {
                        extend: 'pdf',
                        title: '{!! $title !!}  \n generated on: {!! $currentDate !!}',
                    },
                    {
                        extend: 'print',
                        title: '{!! $title !!}  \n generated on: {!! $currentDate !!}',
                    }

                ],
                columns: {!! $columns !!}
            } );
        });
    </script>
@endpush
<div class="relative z-0 px-4 py-5 sm:px-6">
    <div class="lg:px-8"   wire:ignore >
        <table class="table mt-4" id="{{ preg_replace('/[.-]/', '_', $graphDiv)}}">
            <thead>
                @foreach($columns as $column)
                    <th> {{$column['title']}} </th>
                @endforeach
            </thead>
        </table>
    </div>
    <div wire:loading.flex class="absolute inset-0 justify-center items-center z-10 opacity-80 bg-white">
        Updating...
        <svg class="animate-spin h-5 w-5 mr-3 ..." viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="gray" stroke-width="4"></circle>
            <path class="opacity-75"  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
    <div
        x-show="show_help"
        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="transition duration-1000 ease-in-out absolute inset-0 justify-center items-center opacity-90 bg-white px-4 py-5 sm:px-6"
        x-cloak
    >
        {!! $help !!}
    </div>
</div>
