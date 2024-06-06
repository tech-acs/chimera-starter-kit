@props(['stats' => ['total' => '...', 'complete' => '...', 'partial' => '...', 'duplicate' => '...']])

<div class="animate-pulse mb-2">
    <div class="text-sm font-semibold uppercase text-left text-gray-600 tracking-wider mb-1 flex align-middle">
        <div>{{ __('Interview stats') }}</div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($stats as $name => $value)
            <div class="flex rounded-md shadow bg-white p-2">
                <x-chimera::case-icon type="{{ $name }}" class="opacity-75" />
                <div class="flex-1 truncate px-4">
                    <div class="font-medium text-2xl text-gray-900 hover:text-gray-600"><div class="h-6 my-1 w-12 bg-slate-200 rounded"></div></div>
                    <div class="text-gray-400 text-sm">{{ __(ucfirst($name)) }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
