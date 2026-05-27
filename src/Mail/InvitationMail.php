<?php

namespace Uneca\Chimera\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Uneca\Chimera\Models\Invitation;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function build()
    {
        return $this->subject(config('app.name').' registration invitation')
            ->markdown('chimera::mail.invitation')
            ->with(['ttl' => config('chimera.invitation.ttl_hours')]);
    }
}
