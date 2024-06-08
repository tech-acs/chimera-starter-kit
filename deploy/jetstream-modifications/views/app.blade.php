<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" href="{{asset('images/favicon.ico', config('chimera.secure'))}}" />

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        @stack('scripts')
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="flex flex-col min-h-screen">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-grow bg-gray-100">
                {{ $slot }}
            </main>

            <!-- Page Footer -->
            <footer class="bg-white" id="page-footer">
                <div class="mx-auto md:py-6 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                    <div class="mt-2 md:mt-0 text-center text-base text-gray-400 flex">
                        <div>{{ __('Developed by') }}&nbsp;<a href="https://www.uneca.org/data-and-statistics" target="_blank" class="text-blue-400">{{ __('ECA') }}</a></div>
                        <div class="flex ml-6 space-x-2">
                            <div>
                                <a href="mailto:ecastats@un.org" class="text-gray-400 hover:text-gray-500" target="_blank" title="Email us">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"></path>
                                        <path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"></path>
                                    </svg>
                                </a>
                            </div>
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

        <x-chimera::toast-notification />

        @stack('modals')

        @livewireScripts

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, content, preventDefault }) => {
                        const message = {"content": "", "type": "error"}
                        if (status === 419) {
                            message.content = "Your session has expired. Please login again"
                        } else if (status === 500) {
                            message.content = "We encountered a server error."
                        } else {
                            message.content = "Server or connection error."
                        }
                        Livewire.dispatch('notify', message)
                        preventDefault()
                    })
                })
            })
        </script>

    </body>
</html>
