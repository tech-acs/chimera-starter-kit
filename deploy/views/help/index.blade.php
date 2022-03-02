<x-app-layout>

    {{--<x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('User management') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">{{ __('Users can sign-up by using the register link on the home page.') }}
            {{ __('You can then assign users one of the roles you have setup which will then dictate which features they will have access to.') }}</p>
    </x-slot>--}}

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <div class="bg-white">
            <div class="max-w-7xl mx-auto">

                <div class="min-h-full">

                    <div class="py-10">
                        <div class="max-w-3xl mx-auto sm:px-6 lg:max-w-7xl lg:px-8 lg:grid lg:grid-cols-12 lg:gap-8">
                            <div class="hidden lg:block lg:col-span-2">
                                <nav aria-label="Sidebar" class="sticky top-4 divide-y divide-gray-300">
                                    <div class="">
                                        <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider" id="communities-headline">
                                            Dashboard
                                        </p>
                                        <div class="mt-3 space-y-2" aria-labelledby="communities-headline">
                                            <a href="#getting_started" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Getting started</span>
                                            </a>
                                            <a href="#landing_page" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Landing page</span>
                                            </a>
                                            <a href="#user_registration" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">User registration</span>
                                            </a>
                                            <a href="#login_page" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Login page</span>
                                            </a>
                                            <a href="#components" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Components</span>
                                            </a>
                                            <a href="#home_page" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Home page</span>
                                            </a>
                                            <a href="#indicators" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Indicator charts</span>
                                            </a>
                                            <a href="#area_filters" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Area filters</span>
                                            </a>
                                            <a href="#help" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Help and FAQ</span>
                                            </a>
                                            <a href="#user_management" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">User management</span>
                                            </a>
                                            <a href="#database_connection" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">Database connection</span>
                                            </a>
                                            <a href="#user_profile" class="group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:text-gray-900 hover:bg-gray-50">
                                                <span class="truncate">User profile</span>
                                            </a>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                            <main class="lg:col-span-10">

                                <div class="">
                                    <ul role="list" class="space-y-8">
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="getting_started"> Getting Started </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <p>The Census monitoring dashboard is a web-based application developed using Laravel and Plotly.js. The Census dashboard consumes data from a MySQL breakout database(s) to be automatically populated from the census database in a scheduled time interval. It also uses a PostgreSQL database for application management such as user management, access management, database connection settings and geo features.</p>
                                                    <p>The dashboard is a generic application built up to act as a "dashboarding framework" for censuses and survey monitoring.  The application provides building blocks and structure for developers to write codes according to its API to integrate country-specific breakout databases so that charts and graphs are generated based on the country's needs from the database.</p>
                                                    <p>The dashboard is deployed from an already developed code available at our GitHub repository.</p>
                                                    <p>The dashboard uses the following technologies.: </p>
                                                    <ul class="px-8 list-disc list-inside">
                                                        <li>PHP 7.4|8.0</li>
                                                        <li>PostgreSQL 10|11|12</li>
                                                        <li>PostGIS 3</li>
                                                        <li> MySQL 5.6 or greater (recommended is 8.0)</li>
                                                        <li>Redis – caching.</li>
                                                    </ul>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">help</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">manual</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="landing_page"> Landing Page </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="flex-row mt-2 text-sm text-gray-700 space-y-4">
                                                    <div class="flex flex-row">
                                                        <div class="px-4 py-2 m-2 w-32 md:w-64 lg:w-96">
                                                            <p class="w-32 md:w-64 lg:w-96">
                                                                The dashboard is a web-based system accessible through a designated URL in any web browser.  Once the user follows the URL, he/she will be guided to the
                                                                <strong>landing page</strong> which is specifically designed for branding purpose.
                                                                Dashboard owners are free to put any branding and/or census campaigning content in this page.
                                                                This is a page accessible to the public without login requirement.
                                                            </p>
                                                            <br>
                                                            <p class="w-32 md:w-64 lg:w-96">
                                                                The dashboard system is accessible only to registered users, who can click on either the <strong>Get started</strong> button or a <strong>Sign in </strong> link as marked red in the image.
                                                            </p>
                                                        </div>
                                                        <div class="px-4 py-2 m-2">
                                                            <img class="w-full h-full" onerror="this.style.display='none'" src="{{asset('images/help/landing.png', env('SECURE', false))}}" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">help</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">login</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">manual</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="user_registration"> User Registration </a>
                                                        </div>

                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <p>
                                                        Dashboard users get invitation email from the Dashboard Adminstrator.
                                                        The email guides the user to navigate to the <strong>User Registration</strong> page where a user can profide user profile and initial password.  Once registered, the user can proceed to the Login page to access the dashboard as per the predefined set of access privileges.
                                                    </p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">help</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">login</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">manual</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="login_page"> Login Page </a>
                                                        </div>

                                                    </div>

                                                </div>
                                                <div class="flex-row mt-2 text-sm text-gray-700 space-y-4">
                                                    <div class="flex flex-row">
                                                        <div class="px-4 py-2 m-2 w-32 md:w-64 lg:w-96">
                                                            <p class="w-32 md:w-64 lg:w-96">
                                                                Only users who have access right to the dashboard can log into the syste.  The Login page allows the users to login. It has two fields, the User name which is always an email address, and the Password. Once the inputs are verified, the user will proceed to the home page of the dashboard.
                                                                If the user is new, s/he should request the Dashboard Admin to get invitation to register. A link in the invitation email leads to the <strong> Registration  Page</strong>.
                                                            </p>
                                                        </div>
                                                        <div class="px-4 py-2 m-2">
                                                            <img class="w-full h-full" onerror="this.style.display='none'" src="{{asset('images/help/login.png', env('SECURE', false))}}" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">help</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">login</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">manual</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="components"> Dashboard Components </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <p>The dashboard is composed of a set of components which serve users with based on the pre-developed features.  The functionality of these components can be accessed through the menu bar at the top of the page.</p>
                                                    <img class="w-full h-full" onerror="this.style.display='none'" src="{{asset('images/help/menu_bar.png', env('SECURE', false))}}" alt="">
                                                    <p>The major components of the dashboard are the following:</p>
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Home page
                                                    </h2>
                                                    <p>
                                                        The home page presents a structured interview statistics and nation-wide scorecards on selected KPIs such as average interview time, average household size, total population, etc. The interview statistics also outline the number of total cases as well as the number of completed, partial and duplicated cases.  The home page also shows the census period and the last update time of the breakout database.  All these scorecards are grouped into panels, each corresponding to specific census exercises or dictionaries.
                                                    </p>
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Indicator charts
                                                    </h2>
                                                    <p>
                                                        This is where the detail performance as well as demographic indicators are presented in multiple formats including charts (bar chart, stacked bar chart, overlay bar charts, line charts), maps and tabular presentations.  The indicators are grouped into tabs based on census exercises or dictionaries, hence for instance listing, households, population etc.
                                                    </p>
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Help/FAQ
                                                    </h2>
                                                    <p>
                                                        The help is what you are looking at right now, which servs as the user manual of the dashboard. The FAQ can serve as a knowledge repository or a forum where users’ questions and Admin’s responses are documented and presented for future reference.
                                                    </p>
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Management
                                                    </h2>
                                                    <p>
                                                        This component is used to manage users, roles, access history and database configuration. The super admin has the privileges to access all functionalities of the dashboard.  A user with Super Admin role has the right to create roles and invite users to register.  S/he can also apply area restrictions to users so that they view indicators specific to granted areas.  The Admin also uses the management functionality to link the dashboard to the breakout database.  An FAQ question and answer entry is also captured through this management functionality.
                                                    </p>
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        User Profile
                                                    </h2>
                                                    <p>
                                                        Users can edit their profile and change their password by selecting the <strong> Profile </strong> option.
                                                    </p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">help</span>
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="home_page"> Home Page </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="indicators"> Indicator Charts </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="area_filters"> Area Filters </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="help"> Help & FAQ </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="user_management"> User Management </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="database_connection"> Database Connection </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <li class="bg-gray-50 px-4 py-6 shadow sm:p-6 sm:rounded-lg">
                                            <article aria-labelledby="question-title-81614">
                                                <div>
                                                    <div class="flex space-x-3">
                                                        <div class="min-w-0 flex-1 font-bold">
                                                            <a name="user_profile"> User Profile </a>
                                                        </div>
                                                        <div class="flex-shrink-0 self-center flex">

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="mt-2 text-sm text-gray-700 space-y-4">
                                                    <h2 id="question-title-81614" class="mt-4 text-base font-medium text-gray-900">
                                                        Under construction
                                                    </h2>
                                                    <p></p>
                                                </div>
                                                <div class="mt-6 flex justify-between space-x-8">
                                                    <div class="uppercase text-xs text-gray-500">Keywords</div>
                                                    <div class="flex space-x-6">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">indicators</span>
                                                    </div>
                                                </div>
                                            </article>
                                        </li>
                                        <!-- More questions... -->
                                    </ul>
                                </div>
                            </main>

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-app-layout>
