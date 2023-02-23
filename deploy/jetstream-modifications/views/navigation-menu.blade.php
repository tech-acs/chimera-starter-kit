<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}">
                        <x-jet-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex items-center">
                    <x-jet-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                        {{__('Home')}}
                    </x-jet-nav-link>
                @foreach($pages as $route => $page)
                    @can($page->slug)
                        <x-jet-nav-link href="{{ route('page', $route) }}" :active='str(request()->path())->exactly("page/{$page->slug}")'>
                            {{$page->title}}
                        </x-jet-nav-link>
                    @endcan
                @endforeach
                @can('maps')
                    <x-jet-nav-link href="{{ route('map') }}" :active="request()->routeIs('map')">
                        {{ __('Map') }}
                    </x-jet-nav-link>
                @endcan
                @can('reports')
                    <x-jet-nav-link href="{{ route('report') }}" :active="request()->routeIs('report')">
                        {{ __('Reports') }}
                    </x-jet-nav-link>
                @endcan
                @if(\Uneca\Chimera\Models\Faq::count())
                    <x-jet-nav-link href="{{ route('faq') }}" :active="request()->routeIs('faq')">
                        {{ __('FAQ') }}
                    </x-jet-nav-link>
                @endif

                    <livewire:command-palette />
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div class="flex space-x-4">
                    <x-jet-dropdown align="right" width="w-96" contentClasses="py-0 bg-white overflow-hidden">
                        <x-slot name="trigger">
                            <livewire:notification-bell />
                        </x-slot>
                        <x-slot name="content" class="overflow-hidden py-0">
                            <livewire:notification-dropdown />
                        </x-slot>
                    </x-jet-dropdown>

                    <livewire:language-switcher />

                    @can('Super User')
                        <x-jet-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <x-chimera::round-button title="{{ __('Manage dashboard') }}">
                                    <x-chimera::icon.wrench />
                                </x-chimera::round-button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-48">
                                    <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Access Control') }}</div>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('user.index') }}">{{ __('Users') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('role.index') }}">{{ __('Roles') }}</x-jet-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Core Configuration') }}</div>
                                    <x-jet-dropdown-link class="px-6" href="{{route('developer.questionnaire.index')}}">{{ __('Sources') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{route('developer.area-hierarchy.index')}}">{{ __('Area Hierarchy') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('developer.area.index') }}">{{ __('Areas') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('developer.reference-value.index') }}">{{ __('Reference Values') }}</x-jet-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <div class="block px-4 py-2 text-xs text-gray-400">{{ __('Dashboard Elements') }}</div>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('page.index') }}">{{ __('Pages') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('indicator.index') }}">{{ __('Indicators') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('scorecard.index') }}">{{ __('Scorecards') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('manage.report.index') }}">{{ __('Reports') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link class="px-6" href="{{ route('manage.map_indicator.index') }}">{{ __('Map Indicators') }}</x-jet-dropdown-link>
                                    <div class="border-t border-gray-100"></div>
                                    <x-jet-dropdown-link href="{{route('announcement.index')}}">{{ __('Announcements') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link href="{{route('usage_stats')}}">{{ __('Usage Stats') }}</x-jet-dropdown-link>
                                    <x-jet-dropdown-link href="{{route('analytics.index')}}">{{ __('Query Analytics') }}</x-jet-dropdown-link>
                                    {{--<x-jet-dropdown-link href="{{route('manage.faq.index')}}">{{ __('FAQs') }}</x-jet-dropdown-link>--}}
                                </div>
                            </x-slot>
                        </x-jet-dropdown>
                    @endcan
                </div>

                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="ml-3 relative">
                        <x-jet-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition">
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
                                    <x-jet-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-jet-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-jet-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-jet-dropdown-link>
                                    @endcan

                                    <div class="border-t border-gray-100"></div>

                                    <!-- Team Switcher -->
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        {{ __('Switch Teams') }}
                                    </div>

                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-jet-switchable-team :team="$team" />
                                    @endforeach
                                </div>
                            </x-slot>
                        </x-jet-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-jet-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex items-center text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" />
                                    <span class="hidden ml-3 text-gray-700 text-sm font-medium lg:block">{{Auth::user()->name}}</span>
                                    <svg class="hidden flex-shrink-0 ml-1 h-5 w-5 text-gray-400 lg:block" x-description="Heroicon name: solid/chevron-down" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
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

                            <x-jet-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-jet-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-jet-dropdown-link>
                            @endif

                            <div class="border-t border-gray-100"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-jet-dropdown-link href="{{ route('logout') }}"
                                         onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-jet-dropdown-link>
                            </form>
                        </x-slot>
                    </x-jet-dropdown>
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
            <x-jet-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-jet-responsive-nav-link>
            @foreach($pages as $route => $page)
                @can($page->slug)
                    <x-jet-responsive-nav-link href="{{ route('page', $route) }}" :active='str(request()->path())->exactly("page/{$page->slug}")'>
                        {{$page->title}}
                    </x-jet-responsive-nav-link>
                @endcan
            @endforeach
            @can('maps')
                <x-jet-responsive-nav-link href="{{ route('map') }}" :active="request()->routeIs('map')">
                    {{ __('Map') }}
                </x-jet-responsive-nav-link>
            @endcan
            @can('reports')
                <x-jet-responsive-nav-link href="{{ route('report') }}" :active="request()->routeIs('report')">
                    {{ __('Reports') }}
                </x-jet-responsive-nav-link>
            @endcan
            @if(\Uneca\Chimera\Models\Faq::count())
                <x-jet-responsive-nav-link href="{{ route('faq') }}" :active="request()->routeIs('faq')">
                    {{ __('FAQ') }}
                </x-jet-responsive-nav-link>
            @endif
            @can('Super User')
                <div class="border-t border-gray-200"></div>
                <x-jet-responsive-nav-link href="{{ route('user.index') }}">{{ __('Users') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('role.index') }}">{{ __('Roles') }}</x-jet-responsive-nav-link>
                <div class="border-t border-gray-200 border-dotted mx-4"></div>
                <x-jet-responsive-nav-link href="{{ route('developer.questionnaire.index') }}">{{ __('Questionnaires') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('developer.area-hierarchy.index') }}" >{{ __('Area Hierarchy') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('developer.area.index') }}">{{ __('Areas') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('developer.reference-value.index') }}">{{ __('Reference Values') }}</x-jet-responsive-nav-link>
                <div class="border-t border-gray-200 border-dotted mx-4"></div>
                <x-jet-responsive-nav-link href="{{ route('page.index') }}">{{ __('Pages') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('indicator.index') }}">{{ __('Indicators') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('scorecard.index') }}">{{ __('Scorecards') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('manage.report.index') }}">{{ __('Reports') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('manage.map_indicator.index') }}">{{ __('Map Indicators') }}</x-jet-responsive-nav-link>
                <div class="border-t border-gray-200 border-dotted mx-4"></div>
                <x-jet-responsive-nav-link href="{{ route('announcement.index') }}">{{ __('Announcements') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('usage_stats') }}">{{ __('Usage Stats') }}</x-jet-responsive-nav-link>
                <x-jet-responsive-nav-link href="{{ route('analytics.index') }}">{{ __('Query Analytics') }}</x-jet-responsive-nav-link>
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
                <x-jet-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-jet-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-jet-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-jet-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-jet-responsive-nav-link href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                    this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-jet-responsive-nav-link>
                </form>
                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <div class="border-t border-gray-200"></div>

                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-jet-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-jet-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-jet-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-jet-responsive-nav-link>
                    @endcan

                    <div class="border-t border-gray-200"></div>

                    <!-- Team Switcher -->
                    <div class="block px-4 py-2 text-xs text-gray-400">
                        {{ __('Switch Teams') }}
                    </div>

                    @foreach (Auth::user()->allTeams() as $team)
                        <x-jet-switchable-team :team="$team" component="jet-responsive-nav-link" />
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</nav>
