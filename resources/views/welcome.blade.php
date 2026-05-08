<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="{{asset('images/favicon.ico', config('chimera.secure'))}}" />

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

        @vite(['resources/css/app.css'])
    </head>
    <body class="antialiased">

        <div class="relative bg-gray-50 flex flex-col min-h-screen">
        <div class="relative bg-white shadow">
            <div class="mx-auto px-4 sm:px-6">
                <div class="flex justify-between items-center py-6 md:justify-start md:space-x-10">
                    <div class="flex justify-start lg:w-0 lg:flex-1">
                        <a href="#">
                            <x-application-mark class="block h-10 w-auto" />
                        </a>
                    </div>

                    <div class="hidden md:flex items-center justify-end md:flex-1 lg:w-0">
                        @auth
                            <a href="{{ url('/home') }}" class="whitespace-nowrap text-base font-medium text-gray-500 hover:text-gray-900">
                                {{ __('Home') }}
                            </a>
                        @elseguest
                            <a href="{{route('login')}}" class="whitespace-nowrap text-base font-medium text-gray-500 hover:text-gray-900">
                                {{ __('Sign in') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <main class="lg:relative flex-grow">
            <div class="mx-auto max-w-[90%] w-full pb-20 text-center lg:py-24 lg:text-left">
                <div class="px-4 lg:w-2/5 sm:px-8 xl:pr-8">

                    <span class="mb-4 inline-flex items-center gap-x-1.5 rounded-full bg-indigo-100 px-3 py-2 text-sm font-medium text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-400">
  <svg viewBox="0 0 6 6" aria-hidden="true" class="size-1.5 fill-indigo-500 dark:fill-indigo-400">
    <circle r="3" cx="3" cy="3" />
  </svg>
  {{ __('Built with Laravel for performance & scale') }}
</span>
                    <h1 class="tracking-tight font-extrabold text-gray-900 text-4xl sm:text-5xl lg:text-6xl xl:text-7xl">
                        <span class="block xl:inline">{{ __('Smarter Census.') }}</span>
                        <span class="block xl:inline">{{ __('Better Decisions.') }}</span>
                        <span class="block text-indigo-600 xl:inline">{{ __('Stronger Impact.') }}</span>
                    </h1>
                    <p class="mt-3 max-w-md mx-auto text-lg text-gray-500 sm:text-xl md:mt-5 md:max-w-3xl">
                    {{ __('description of dashboard on landing page') }}
                    </p>
                    <div class="mt-10 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <a href="{{ url('/home') }}" class="w-full flex items-center gap-x-1.5 justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="-ml-0.5 mr-2 -mt-1 size-5">
                                    <path fill-rule="evenodd" d="M9.315 7.584C12.195 3.883 16.695 1.5 21.75 1.5a.75.75 0 0 1 .75.75c0 5.056-2.383 9.555-6.084 12.436A6.75 6.75 0 0 1 9.75 22.5a.75.75 0 0 1-.75-.75v-4.131A15.838 15.838 0 0 1 6.382 15H2.25a.75.75 0 0 1-.75-.75 6.75 6.75 0 0 1 7.815-6.666ZM15 6.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" clip-rule="evenodd" />
                                    <path d="M5.26 17.242a.75.75 0 1 0-.897-1.203 5.243 5.243 0 0 0-2.05 5.022.75.75 0 0 0 .625.627 5.243 5.243 0 0 0 5.022-2.051.75.75 0 1 0-1.202-.897 3.744 3.744 0 0 1-3.008 1.51c0-1.23.592-2.323 1.51-3.008Z" />
                                </svg>
                                {{ __('Get Started') }}
                            </a>
                        </div>
                    </div>

                    <span class="text-sm mt-6 inline-flex items-center gap-x-1.5 text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-indigo-700">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
</svg>

                        {{ __('Field proven in multiple African censuses') }}
                    </span>
                </div>
            </div>
            <div class="relative w-full h-64 sm:h-72 md:h-96 lg:absolute lg:inset-y-0 lg:right-0 lg:w-3/5 lg:h-full">
                <img class="absolute inset-0 w-full h-full object-center object-cover min-w-96" src="{{ asset('images/hero.png', config('chimera.secure')) }}" alt="">
            </div>
        </main>

        <!-- Page Footer -->
        <footer class="bg-white" id="page-footer">
            <div class="mx-auto md:py-6 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                <div class="mt-2 md:mt-0 text-center text-base text-gray-400 flex">
                    <div>{{ __('Developed by') }}&nbsp;<a href="https://www.uneca.org/data-and-statistics" target="_blank" class="text-blue-400">{{ __('ECA') }}</a></div>
                    <div class="flex ml-6 space-x-2">
                        <div>
                            <a href="http://www.facebook.com/EconomicCommissionforAfrica" class="text-gray-400 hover:text-gray-500" target="_blank" title="ECA on Facebook">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                        <div>
                            <a href="http://www.youtube.com/unecaVideo/" class="text-gray-400 hover:text-gray-500" target="_blank" title="ECA on Youtube">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        </div>
                        <div>
                            <a href="https://twitter.com/eca_official" class="text-gray-400 hover:text-gray-500" target="_blank" title="ECA on Twitter">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                        </div>
                        <div>
                            <a href="https://tech-acs.github.io/chimera-docs/" class="text-gray-400 hover:text-gray-500" target="_blank" title="Documentation">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                    <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0m-18 -13a9 9 0 0 1 9 0a9 9 0 0 1 9 0m-18 0l0 13m9 -13l0 13m9 -13l0 13"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="mt-2 md:mt-0 text-center text-base text-gray-400 flex">
                    <div>{{ __('Owned by') }}&nbsp;<a href="{{ settings('app_owner_url', '#') }}" target="_blank" class="text-blue-400">{{ settings('app_owner_name', 'ECA') }}</a></div>
                </div>
            </div>
        </footer>
    </div>

    </body>
</html>
