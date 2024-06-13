<div>
    <div class="flex justify-end">
        <x-button wire:click="$toggle('showSingleInviteForm')" wire:loading.attr="disabled">{{ __('Invite New User') }}</x-button>
        <livewire:bulk-inviter />
    </div>
    <div class="py-2 align-middle inline-block min-w-full">
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200" wire:model="records">
                <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Email') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Status') }}
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ __('Role to assign') }}
                    </th>
                    <th scope="col" class="relative px-6 py-3"></th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($records as $record)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center text-sm text-gray-500">
                                {{$record->email}}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-gray-800">
                                {{$record->status}}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-gray-800">
                                {{$record->role}}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-400">
                            @if($record->is_expired)
                                <a class="text-indigo-600 hover:text-indigo-900 cursor-pointer" wire:click.prevent="renew({{$record->id}})">{{ __('Renew') }}</a>
                            @else
                                <a class="text-indigo-600 hover:text-indigo-900 cursor-pointer" wire:click.prevent="showLinkModal({{$record->id}})">{{ __('Show link') }}</a>
                                |
                                <a class="text-indigo-600 hover:text-indigo-900 cursor-pointer" wire:click.prevent="resendEmail({{$record->id}})">{{ __('Resend email') }}</a>
                            @endif
                             |
                            <a class="text-red-600 hover:text-red-800 cursor-pointer" wire:click.prevent="delete({{$record->id}})">{{ __('Delete') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap">
                            <div class="flex justify-center text-sm text-gray-500">
                                {{ __('No records to display') }}
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-dialog-modal wire:model="showSingleInviteForm">
        <x-slot name="title">
            {{ __('Invite new user') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Using the email address of the user you want to invite, a registration link will be generated which you can then send to the prospective user.') }}
            {{ __('The link will expire in :ttl hours.', ['ttl' => config('chimera.invitation.ttl_hours')]) }}
            <div class="mt-6">
                <x-label for="email" value="{{ __('Email address') }}" />
                <x-input id="email" type="email" class="mt-1 block w-2/3" wire:model.defer="email" />
                <x-input-error for="email" class="mt-2" />
                @if(config('chimera.emailing_enabled'))
                    <x-label>
                        <x-checkbox name="send_email" class="mr-1" wire:model="sendEmail" checked /> {{ __('send invitation email') }}
                    </x-label>
                @endif
            </div>
            <div class="mt-6">
                <x-label for="role" value="{{ __('Role to assign') }}" />
                <select wire:model="role" id="location" name="location" class="mt-1 block w-2/3 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">{{ __('Will assign later') }}</option>
                    @foreach($roles as $r)
                        <option value="{{$r->name}}">{{$r->name}}</option>
                    @endforeach
                </select>
                <x-input-error for="role" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-action-message class="mr-3 inline-flex items-center" on="invited">
                {{ __('Invited.') }}
            </x-action-message>

            <x-secondary-button wire:click="$toggle('showSingleInviteForm')" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-secondary-button>

            <x-button class="ml-2" wire:click="submit" wire:loading.attr="disabled">
                {{ __('Invite') }}
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <x-dialog-modal wire:model="showLink">
        <x-slot name="title">
            {{ __('User registration link') }}
        </x-slot>

        <x-slot name="content">
            <script>
                function copyToClipboard(textToCopy) {
                    // navigator clipboard api needs a secure context (https)
                    if (navigator.clipboard && window.isSecureContext) {
                        return navigator.clipboard.writeText(textToCopy);
                    } else {
                        const temp = document.createElement("input")
                        temp.value = textToCopy
                        document.body.appendChild(temp)
                        temp.select()
                        document.execCommand("Copy")
                        document.body.removeChild(temp)
                    }
                }
            </script>
            <div class="text-sm text-gray-500 flex-wrap">
                {{ __('Here is the user\'s unique registration link. They can use it to register before it expires.') }}<br>
                {{ __('Please copy and send it to them.') }}
            </div>
            <div class="flex items-center mt-4" x-data="{copied: false, initialized: false}">
                <input type="text" id="invite_link" readonly class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md" value="{{$link}}">
                <button @click="copyToClipboard(document.getElementById('invite_link').value); copied=true; setTimeout(()=>{copied=false}, 2000);" class="hidden sm:flex sm:items-center sm:justify-center relative w-9 h-9 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 text-gray-400 hover:text-gray-600 group ml-1.5" :style="copied ? 'color:#06B6D4' : ''">
                    <div x-show="copied" style="display:none" class="absolute inset-x-0 bottom-full mb-2.5 flex justify-center" x-transition:enter="transform ease-out duration-200 transition origin-bottom" x-transition:enter-start="scale-95 translate-y-0.5 opacity-0" x-transition:enter-end="scale-100 translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                      <div class="bg-gray-900 text-white rounded-md text-xs leading-4 tracking-wide font-semibold uppercase py-1 px-3 filter drop-shadow-md">
                        <svg width="16" height="6" viewBox="0 0 16 6" class="text-gray-900 absolute top-full left-1/2 -mt-px -ml-2">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M15 0H1V1.00366V1.00366V1.00371H1.01672C2.72058 1.0147 4.24225 2.74704 5.42685 4.72928C6.42941 6.40691 9.57154 6.4069 10.5741 4.72926C11.7587 2.74703 13.2803 1.0147 14.9841 1.00371H15V0Z" fill="currentColor"></path>
                        </svg>
                        {{ __('Copied!') }}
                      </div>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="size-6 transform group-hover:rotate-[-4deg] transition" :style="copied ? '--tw-rotate:-8deg;' : ''" style="">
                        <path fill="currentColor" d="M9 18q-.825 0-1.412-.587T7 16V4q0-.825.588-1.412T9 2h9q.825 0 1.413.588T20 4v12q0 .825-.587 1.413T18 18zm0-2h9V4H9zm-4 6q-.825 0-1.412-.587T3 20V6h2v14h11v2zm4-6V4z"/>
                    </svg>
                </button>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('showLink')" wire:loading.attr="disabled">
                {{ __('Ok') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

    <x-dialog-modal wire:model="showResult">
        <x-slot name="title">
            {{ __($resultTitle) }}
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-500 flex-wrap">
                {!! __($resultBody) !!}
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('showResult')" wire:loading.attr="disabled">
                {{ __('Ok') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
