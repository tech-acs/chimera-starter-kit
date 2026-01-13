<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex items-center">
                    <x-nav-link href="{{ route('home') }}" class="!text-base" :active="request()->routeIs('home')">
                        {{__('Home')}}
                    </x-nav-link>
                @foreach($pages as $route => $page)
                    @can($page->slug)
                        <x-nav-link href="{{ route('page', $route) }}" class="!text-base" :active='str(request()->path())->exactly("page/{$page->slug}")'>
                            {{$page->title}}
                        </x-nav-link>
                    @endcan
                @endforeach
                @if(settings('show_map_menu'))
                    <x-nav-link href="{{ route('map') }}" class="!text-base" :active="request()->routeIs('map')">
                        {{ __('Map') }}
                    </x-nav-link>
                @endif
                @if(settings('show_reports_menu'))
                    <x-nav-link href="{{ route('report') }}" class="!text-base" :active="request()->routeIs('report')">
                        {{ __('Reports') }}
                    </x-nav-link>
                @endif
                @if(settings('show_area_insights_menu', true))
                    <x-nav-link href="{{ route('area-insights') }}" class="!text-base" :active="request()->routeIs('area-insights', 'area-insights.show')">
                        {{ __('Area Insights') }}
                    </x-nav-link>
                @endif

                    <livewire:command-palette />
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="flex space-x-4">

                    @can('developer-mode')
                        <div title="{{ __('Warning') }}"
                             onclick="Livewire.dispatch('notify', {'content': 'You are in developer mode. If this is a production server, make sure to turn it off right away.', 'type': 'error'})"
                             class="inline-flex relative items-center animate-ping flex-shrink-0 rounded-full p-1 cursor-pointer"
                        >
                            <x-chimera::icon.exclamation-triangle />
                        </div>
                    @endcan

                    <x-dropdown align="right" contentClasses="py-0 bg-white overflow-hidden w-96">
                        <x-slot name="trigger">
                            <livewire:notification-bell />
                        </x-slot>
                        <x-slot name="content" class="overflow-hidden py-0">
                            <livewire:notification-dropdown />
                        </x-slot>
                    </x-dropdown>

                    <livewire:language-switcher />

                    @can('Super User')
                        <x-dropdown align="right">
                            <x-slot name="trigger">
                                <x-chimera::round-button title="{{ __('Manage dashboard') }}">
                                    <x-chimera::icon.wrench />
                                </x-chimera::round-button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-48">
                                    <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Access Control') }}</div>
                                    <x-dropdown-link class="px-6" href="{{ route('user.index') }}">{{ __('Users') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('role.index') }}">{{ __('Roles') }}</x-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Core Configuration') }}</div>
                                    <x-dropdown-link class="px-6" href="{{route('developer.data-source.index')}}">{{ __('Data Sources') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{route('developer.area-hierarchy.index')}}">{{ __('Area Hierarchy') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('developer.area.index') }}">{{ __('Areas') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('developer.reference-value.index') }}">{{ __('Reference Values') }}</x-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Dashboard Artefacts') }}</div>
                                    <x-dropdown-link class="px-6" href="{{ route('page.index') }}">{{ __('Pages') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('indicator.index') }}">{{ __('Indicators') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('scorecard.index') }}">{{ __('Scorecards') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('gauge.index') }}">{{ __('Gauges') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('manage.report.index') }}">{{ __('Reports') }}</x-dropdown-link>
                                    <x-dropdown-link class="px-6" href="{{ route('manage.map_indicator.index') }}">{{ __('Map Indicators') }}</x-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <x-dropdown-link href="{{route('announcement.index')}}">{{ __('Announcements') }}</x-dropdown-link>
                                    <x-dropdown-link href="{{route('usage_stats')}}">{{ __('Usage Stats') }}</x-dropdown-link>
                                    <x-dropdown-link href="{{route('analytics.index')}}">{{ __('Query Analytics') }}</x-dropdown-link>
                                    <x-dropdown-link href="{{route('setting.edit')}}">{{ __('Settings') }}</x-dropdown-link>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    @endcan
                </div>

                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-base leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition">
                                        {{ Auth::user()->currentTeam->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-60">
                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    <div class="border-t border-gray-100"></div>

                                    <!-- Team Switcher -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Switch Teams') }}
                                    </div>

                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-switchable-team :team="$team" />
                                    @endforeach
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex items-center text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" />
                                    <span class="hidden ml-3 text-gray-700 text-base font-medium lg:block">{{Auth::user()->name}}</span>
                                    <svg class="hidden flex-shrink-0 ml-1 h-5 w-5 text-gray-400 lg:block" x-description="Heroicon name: solid/chevron-down" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-base leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                                        {{ Auth::user()->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('Manage Account') }}
                            </div>

                            <x-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <div class="mr-4 flex items-center space-x-4">
                    <div class="-mt-2"><livewire:command-palette /></div>
                    <a href="{{ route('notification.index') }}"><livewire:notification-bell /></a>
                    <livewire:language-switcher />
                </div>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden h-96 overflow-y-auto">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            @foreach($pages as $route => $page)
                @can($page->slug)
                    <x-responsive-nav-link href="{{ route('page', $route) }}" :active='str(request()->path())->exactly("page/{$page->slug}")'>
                        {{$page->title}}
                    </x-responsive-nav-link>
                @endcan
            @endforeach
            @if(settings('show_map_menu'))
                <x-responsive-nav-link href="{{ route('map') }}" :active="request()->routeIs('map')">
                    {{ __('Map') }}
                </x-responsive-nav-link>
            @endif
            @if(settings('show_reports_menu'))
                <x-responsive-nav-link href="{{ route('report') }}" :active="request()->routeIs('report')">
                    {{ __('Reports') }}
                </x-responsive-nav-link>
            @endif
            @can('Super User')
                <div class="border-t border-gray-200"></div>
                <x-responsive-nav-link href="{{ route('user.index') }}">{{ __('Users') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('role.index') }}">{{ __('Roles') }}</x-responsive-nav-link>
                <div class="border-t border-gray-200 border-dotted mx-4"></div>
                <x-responsive-nav-link href="{{ route('developer.data-source.index') }}">{{ __('Data Sources') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('developer.area-hierarchy.index') }}" >{{ __('Area Hierarchy') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('developer.area.index') }}">{{ __('Areas') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('developer.reference-value.index') }}">{{ __('Reference Values') }}</x-responsive-nav-link>
                <div class="border-t border-gray-200 border-dotted mx-4"></div>
                <x-responsive-nav-link href="{{ route('page.index') }}">{{ __('Pages') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('indicator.index') }}">{{ __('Indicators') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('scorecard.index') }}">{{ __('Scorecards') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('manage.report.index') }}">{{ __('Reports') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('manage.map_indicator.index') }}">{{ __('Map Indicators') }}</x-responsive-nav-link>
                <div class="border-t border-gray-200 border-dotted mx-4"></div>
                <x-responsive-nav-link href="{{ route('announcement.index') }}">{{ __('Announcements') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('usage_stats') }}">{{ __('Usage Stats') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('analytics.index') }}">{{ __('Query Analytics') }}</x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('setting.edit') }}">{{ __('Settings') }}</x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="flex-shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-gray-200"></div>

                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    <div class="border-t border-gray-200"></div>

                    <!-- Team Switcher -->
                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Switch Teams') }}
                    </div>

                    @foreach (Auth::user()->allTeams() as $team)
                        <x-switchable-team :team="$team" component="jet-responsive-nav-link" />
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</nav>
