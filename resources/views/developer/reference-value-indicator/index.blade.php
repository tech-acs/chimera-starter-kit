<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('Reference Value Indicators') }}
        </h3>
        <p class="mt-2 max-w-7xl text-sm text-gray-500">
            {{ __('Select an indicator to manage its reference values.') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />
        <x-chimera::error-display />

        <div class="text-right">
            @can('developer-mode')
                <a href="{{route('developer.reference-value-indicator.create')}}"><x-button>{{ __('Create new') }}</x-button></a>
            @endcan
        </div>

        <div class="mt-2 flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg" x-data="confirmedDeletion">
                        <x-chimera::delete-confirmation />

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Indicator') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Description') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('# Values') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($referenceValueIndicators as $referenceValueIndicator)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $referenceValueIndicator->indicator }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $referenceValueIndicator->description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{ $referenceValueIndicator->reference_values_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('developer.reference-value-indicator.edit', $referenceValueIndicator) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                                        @can('developer-mode')
                                            <span class="text-gray-400 px-1">|</span>
                                            <a href="{{ route('developer.reference-value-indicator.destroy', $referenceValueIndicator) }}" x-on:click.prevent="confirmThenDelete($el)" class="text-red-600">{{ __('Delete') }}</a>
                                        @endcan
                                        <span class="text-gray-400 px-1">|</span>
                                        <a href="{{ route('developer.reference-value-indicator.reference-value.index', ['reference_value_indicator' => $referenceValueIndicator]) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ __('Import Values') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-400">
                                        {{ __('There are no records to display') }}
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
