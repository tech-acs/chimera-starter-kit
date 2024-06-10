<x-app-layout>

    <x-slot name="header">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ __('User management') }}
        </h3>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">
            {{ __('Users can sign-up when they receive their unique registration link (invite).') }}
            {{ __('You can then assign users one of the roles you have setup which will then dictate which features they will have access to.') }}
        </p>
    </x-slot>

    <div class="flex flex-col max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <x-chimera::message-display />

        <div x-cloak x-data="{
                selectedId: null,
                init() {
                    // Set the first available tab on the page on page load.
                    this.$nextTick(() => this.tabTo('users'))
                },
                tabTo(id) {
                    this.selectedId = id
                },
                isSelected(id) {
                    return this.selectedId === id
                }
            }"
        >
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a
                            @click="tabTo('users')"
                            :class="isSelected('users') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            {{ __('Users') }}
                            <span
                                :class="isSelected('users') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'"
                                class="bg-gray-200 text-gray-900 hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block"
                            >{{ $users_count }}</span>
                        </a>

                        <a
                            @click="tabTo('invitations')"
                            :class="isSelected('invitations') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                            class="cursor-pointer text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            {{ __('Invitations') }}
                            <span
                                :class="isSelected('invitations') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'"
                                class="bg-gray-200 text-gray-900 hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block"
                            >{{ $invitations_count }}</span>
                        </a>
                    </nav>
                </div>
            </div>
            <div>
                <section id="users" x-show="isSelected('users')" class="py-4">
                    <x-chimera-smart-table :$smartTableData custom-action-sub-view='chimera::user.custom-action' />
                </section>
                <section id="invitations" x-show="isSelected('invitations')" class="py-4">
                    <livewire:invitation-manager />
                </section>
            </div>
        </div>

    </div>

</x-app-layout>
