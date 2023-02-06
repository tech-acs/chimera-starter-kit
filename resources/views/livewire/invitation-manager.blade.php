<div>
    <div class="flex justify-end py-2">
        <x-jet-button wire:click="$toggle('showSingleInviteForm')" wire:loading.attr="disabled">{{ __('Invite New User') }}</x-jet-button>
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
                                <a class="text-indigo-600 hover:text-indigo-900 cursor-pointer" wire:click.prevent="showLink({{$record->id}})">{{ __('Show link') }}</a>
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

    <x-jet-dialog-modal wire:model="showSingleInviteForm">
        <x-slot name="title">
            {{ __('Invite new user') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Using the email address of the user you want to invite, a registration link will be generated which you can then send to the prospective user.') }}
            {{ __('The link will expire in :ttl hours.', ['ttl' => config('chimera.invitation.ttl_hours')]) }}
            <div class="mt-6">
                <x-jet-label for="email" value="{{ __('Email address') }}" />
                <x-jet-input id="email" type="email" class="mt-1 block w-2/3" wire:model.defer="email" />
                <x-jet-input-error for="email" class="mt-2" />
                @if(config('chimera.emailing_enabled'))
                    <x-jet-label>
                        <x-jet-checkbox name="send_email" class="mr-1" wire:model="sendEmail" checked /> {{ __('send invitation email') }}
                    </x-jet-label>
                @endif
            </div>
            <div class="mt-6">
                <x-jet-label for="role" value="{{ __('Role to assign') }}" />
                <select wire:model="role" id="location" name="location" class="mt-1 block w-2/3 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">{{ __('Will assign later') }}</option>
                    @foreach($roles as $r)
                        <option value="{{$r->name}}">{{$r->name}}</option>
                    @endforeach
                </select>
                <x-jet-input-error for="role" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-action-message class="mr-3 inline-flex" on="invited">
                {{ __('Invited.') }}
            </x-jet-action-message>

            <x-jet-secondary-button wire:click="$toggle('showSingleInviteForm')" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="submit" wire:loading.attr="disabled">
                {{ __('Invite') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>

    <x-jet-dialog-modal wire:model="showLink">
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
                        // text area method
                        let textArea = document.createElement("textarea");
                        textArea.value = textToCopy;
                        // make the textarea out of viewport
                        textArea.style.position = "fixed";
                        textArea.style.left = "-999999px";
                        textArea.style.top = "-999999px";
                        document.body.appendChild(textArea);
                        textArea.focus();
                        textArea.select();
                        document.execCommand('copy')
                        return new Promise((res, rej) => {
                            // here the magic happens
                            document.execCommand('copy') ? res() : rej();
                            textArea.remove();
                        });
                    }
                }
            </script>
            <div class="text-sm text-gray-500 flex-wrap">
                {{ __('Here is the user\'s unique registration link. They can use it to register before it expires.') }}<br>
                {{ __('Please copy and send it to them.') }}
            </div>
            <div class="flex items-center mt-4" x-data="{copied: false, initialized: false}">
                <input type="text" id="invite_link" readonly class="w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md" value="{{$link}}">
                <button class="hidden sm:flex sm:items-center sm:justify-center relative w-9 h-9 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 text-gray-400 hover:text-gray-600 group ml-1.5" :style="copied ? 'color:#06B6D4' : ''" @click="copyToClipboard(document.getElementById('invite_link').value).then(()=>{copied=true;copyTimeout=setTimeout(()=>{copied=false},1500)})" style="">
                    <div x-show="copied" style="display:none" class="absolute inset-x-0 bottom-full mb-2.5 flex justify-center" x-transition:enter="transform ease-out duration-200 transition origin-bottom" x-transition:enter-start="scale-95 translate-y-0.5 opacity-0" x-transition:enter-end="scale-100 translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                      <div class="bg-gray-900 text-white rounded-md text-xs leading-4 tracking-wide font-semibold uppercase py-1 px-3 filter drop-shadow-md">
                        <svg width="16" height="6" viewBox="0 0 16 6" class="text-gray-900 absolute top-full left-1/2 -mt-px -ml-2">
                          <path fill-rule="evenodd" clip-rule="evenodd" d="M15 0H1V1.00366V1.00366V1.00371H1.01672C2.72058 1.0147 4.24225 2.74704 5.42685 4.72928C6.42941 6.40691 9.57154 6.4069 10.5741 4.72926C11.7587 2.74703 13.2803 1.0147 14.9841 1.00371H15V0Z" fill="currentColor"></path>
                        </svg>
                        {{ __('Copied!') }}
                      </div>
                    </div>
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" class="stroke-current transform group-hover:rotate-[-4deg] transition" :style="copied ? '--tw-rotate:-8deg;' : ''" style="">
                        <path d="M12.9975 10.7499L11.7475 10.7499C10.6429 10.7499 9.74747 11.6453 9.74747 12.7499L9.74747 21.2499C9.74747 22.3544 10.6429 23.2499 11.7475 23.2499L20.2475 23.2499C21.352 23.2499 22.2475 22.3544 22.2475 21.2499L22.2475 12.7499C22.2475 11.6453 21.352 10.7499 20.2475 10.7499L18.9975 10.7499" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M17.9975 12.2499L13.9975 12.2499C13.4452 12.2499 12.9975 11.8022 12.9975 11.2499L12.9975 9.74988C12.9975 9.19759 13.4452 8.74988 13.9975 8.74988L17.9975 8.74988C18.5498 8.74988 18.9975 9.19759 18.9975 9.74988L18.9975 11.2499C18.9975 11.8022 18.5498 12.2499 17.9975 12.2499Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M13.7475 16.2499L18.2475 16.2499" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M13.7475 19.2499L18.2475 19.2499" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        <g :class="[copied ? '' : 'opacity-0', initialized ? 'transition-opacity' : '']" class="opacity-0 transition-opacity">
                            <path d="M15.9975 5.99988L15.9975 3.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M19.9975 5.99988L20.9975 4.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M11.9975 5.99988L10.9975 4.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </g>
                    </svg>
                </button>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('showLink')" wire:loading.attr="disabled">
                {{ __('Ok') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>

    <x-jet-dialog-modal wire:model="showResult">
        <x-slot name="title">
            {{ __($resultTitle) }}
        </x-slot>

        <x-slot name="content">
            <div class="text-sm text-gray-500 flex-wrap">
                {!! __($resultBody) !!}
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-jet-secondary-button wire:click="$toggle('showResult')" wire:loading.attr="disabled">
                {{ __('Ok') }}
            </x-jet-secondary-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
