<div x-cloak x-data="{
                selectedId: null,
                init() {
                    // Set the first available tab on the page on page load.
                    this.$nextTick(() => this.select('users'))
                },
                select(id) {
                    this.selectedId = id
                },
                isSelected(id) {
                    return this.selectedId === id
                }
            }"
>
    {{--Tabs--}}
    <div class="hidden sm:block">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a
                    @click="select('users')"
                    :class="isSelected('users') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                    class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                >
                    Users
                    <span
                        :class="isSelected('users') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'"
                        class="bg-gray-200 text-gray-900 hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block"
                    >{{ $users_count }}</span>
                </a>

                <a
                    @click="select('invitations')"
                    :class="isSelected('invitations') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200'"
                    class="cursor-pointer border-transparent text-gray-500 whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm"
                >
                    Invitations
                    <span
                        :class="isSelected('invitations') ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'"
                        class="bg-gray-200 text-gray-900 hidden ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium md:inline-block"
                    >{{ $invitations_count }}</span>
                </a>
            </nav>
        </div>
    </div>
    {{--Content--}}
    <div>
        <section id="users" x-show="isSelected('users')">
            {{--Tab content--}}
        </section>
        <section id="invitations" x-show="isSelected('invitations')">
            {{--Tab content--}}
        </section>
    </div>
</div>
