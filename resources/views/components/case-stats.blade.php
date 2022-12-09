<dl class="grid grid-cols-1 rounded-md border bg-white divide-y divide-gray-200 md:grid-cols-4 md:divide-y-0 md:divide-x">
    @foreach($stats as $name => $value)
        <div class="relative">
            <x-chimera::case-icon :type="$name" />
            <div class="p-4 sm:p-5">
                <div class="flex justify-end">
                    <dt class="text-sm font-normal text-gray-900 text-right">
                        {{ ucfirst($name) }}
                    </dt>
                </div>
                <dd class="mt-1 flex justify-end items-center md:block lg:flex">
                    <div class="flex items-baseline ml-2 text-2xl font-semibold">
                        {{ $value }}
                    </div>
                </dd>
            </div>
        </div>
    @endforeach
</dl>
