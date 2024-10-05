<div class="p-4 border rounded-md bg-white flex" wire:poll.1s="checkLogEntries()">
    <div class="mr-6">
        @foreach($groupedIndicators as $dataSource => $indicators)
            <div class="border-b border-gray-200 pb-5">
                <h3 class="text-base font-semibold leading-6 text-gray-900">{{ $dataSource }}</h3>
            </div>
            <div class="pl-4 pt-4 space-y-4">
                @foreach($indicators as $indicator)
                    <div wire:click="takeXRay({{ $indicator->id }})" class="p-2 border rounded-md bg-gray-50 cursor-pointer hover:bg-blue-50">
                        <span>{{ $indicator->title }}</span>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    <div class="flex-1 bg-black text-lime-300 pl-6" >
        <div id="the-matrix" class="overflow-y-auto space-y-4" style="height: calc(100vh - 240px);" x-data="xRayIlluminator" @x-ray-film.window="showFilm">
            <template x-for="(film, index) in films" :key="index">
                <div class="border rounded border-green-700 p-3 mr-1">
                    <div x-html="film.name" class="bg-green-300 text-black p-1 px-2"></div>
                    <div class="text-white font-semibold">SQL</div>
                    <div x-html="film.sql" class="w-1/2"></div>
                    <div class="text-white font-semibold mt-2">Query result</div>
                    <div x-html="film.queryResult" class="text-sm"></div>
                    <div class="text-white font-semibold mt-2">Join type: <span class="text-lime-300 font-normal" x-text="film.joinType">-</span></div>
                    <div class="text-white font-semibold mt-2">Final result</div>
                    <div x-html="film.finalResult" class="text-sm"></div>
                </div>
            </template>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('xRayIlluminator', () => ({
                films: [],
                showFilm(event) {
                    //console.log(event.detail.film);
                    let film = event.detail.film
                    this.films.push({...film, queryResult: JSON.stringify(film.queryResult), finalResult: JSON.stringify(film.finalResult)})
                }
            }))
        })

        // Set up MutationObserver to detect changes in the div
        let targetElement = document.getElementById('the-matrix');

        let observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    // Content has changed, scroll to bottom
                    targetElement.scrollTop = targetElement.scrollHeight;
                }
            });
        });

        // Configuration for observing child list changes
        let config = { childList: true };

        // Start observing
        observer.observe(targetElement, config);
    </script>
</div>
