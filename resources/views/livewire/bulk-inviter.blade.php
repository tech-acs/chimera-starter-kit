<div>
    <x-jet-button wire:click="$toggle('showBulkInviteForm')" wire:loading.attr="disabled" class="ml-3">{{ __('Bulk Invite') }}</x-jet-button>

    <x-jet-dialog-modal wire:model="showBulkInviteForm">
        <x-slot name="title">
            {{ __('Invite multiple users') }}
        </x-slot>

        <x-slot name="content">
            <div class="mb-4">
                <p class="mb-2">
                    You can invite multiple users at once by importing a list of email addresses from an Excel file.
                </p>
                <span class="mb-2 text-sm text-gray-600 italic">
                    <p class="mb-2">The file needs to have at least one column named <span class="font-semibold">email</span> that contains the email addresses.</p>
                    <p>It can also optionally have another column called <span class="font-semibold">role</span> that contains the role to assign.</p>
                </span>
            </div>

            <div class="mt-5">
                <div>
                    <div class="flex flex-grow items-center">
                        <label for="file" class="flex justify-between w-2/3 rounded-md sm:text-sm border border-gray-300">
                            <span id="file_label" class="my-auto pl-4 text-gray-700">{{ $file?->getClientOriginalName() ?? "Choose your file" }}</span>
                            <div class="relative inline-flex items-center hover:bg-gray-100 cursor-pointer space-x-2 px-4 py-2 border-0 border-l rounded-r-md border-gray-300 text-sm font-medium text-gray-700 bg-gray-50">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"></path></svg>
                                <span>Browse</span>
                            </div>
                        </label>
                        <input type="file" id="file" name="file" wire:model="file" onchange="document.getElementById('file_label').innerText=this.files[0].name;" class="hidden">
                        @if ($fileAccepted)
                            <x-chimera::icon.accepted />
                        @endif
                        @if($errors->has('file'))
                            <x-chimera::icon.rejected />
                        @endif
                    </div>
                </div>
                @if($errors->has('file'))
                    <x-jet-input-error for="file" />
                @else
                    <div class="text-xs text-gray-500 mt-1">
                        You must upload a spreadsheet (.xlsx or .csv)
                    </div>
                @endif
            </div>
            <div class="mt-5">
                @if(config('chimera.emailing_enabled'))
                    <x-jet-label>
                        <x-jet-checkbox name="send_email" class="mr-1" wire:model="sendEmails" /> {{ __('send invitation emails') }}
                    </x-jet-label>
                @endif
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-jet-action-message class="mr-3 inline-flex items-center" on="processing">
                {{ __('Invites are being processed...') }}
            </x-jet-action-message>

            <x-jet-secondary-button wire:click="$toggle('showBulkInviteForm')" wire:loading.attr="disabled">
                {{ __('Close') }}
            </x-jet-secondary-button>

            <x-jet-button class="ml-2" wire:click="invite" wire:loading.attr="disabled" onclick="setTimeout(() => Livewire.emit('pleaseHideForm'), 3000);">
                {{ __('Invite') }}
            </x-jet-button>
        </x-slot>
    </x-jet-dialog-modal>
</div>
