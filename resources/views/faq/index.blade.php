<x-app-layout>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <div class="bg-white">
            <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-20 lg:px-8">
                <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                    <div>
                        <h2 class="text-3xl font-extrabold text-gray-900">
                            {{ __('Frequently Asked Questions') }}
                        </h2>
                        <p class="mt-4 text-lg text-gray-500">{{ __('Can’t find the answer you’re looking for? Reach out to the administrator or') }} <a href="mailto:ecastats@un.org?subject=Mail from ECA (Ghana) Census Dashboard" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('email us') }}</a>.</p>
                    </div>
                    <div class="mt-12 lg:mt-0 lg:col-span-2">
                        <dl class="space-y-12">
                            @forelse($records as $record)
                                <div>
                                    <dt class="text-lg leading-6 font-medium text-gray-900">
                                        {{ $record->question }}
                                    </dt>
                                    <dd class="mt-2 text-base text-gray-500">
                                        {!! $record->answer !!}
                                    </dd>
                                </div>
                            @empty
                                <div class="leading-10 text-lg">
                                    {{ __('The dashboard manager hasn’t added any frequently asked questions.') }}
                                </div>
                            @endforelse
                        </dl>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
