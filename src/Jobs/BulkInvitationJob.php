<?php

namespace Uneca\Chimera\Jobs;

use Uneca\Chimera\Mail\InvitationMail;
use Uneca\Chimera\Models\Invitation;
use Uneca\Chimera\Notifications\TaskCompletedNotification;
use Uneca\Chimera\Notifications\TaskFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Spatie\SimpleExcel\SimpleExcelReader;

class BulkInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function __construct(private string $filePath, private bool $hasRoleColumn, private bool $sendEmails, private User $user)
    {
    }

    public function handle()
    {
        $expiresAt = now()->addHours(config('chimera.invitation.ttl_hours'));
        $totalCount = 0;
        $successCount = 0;
        SimpleExcelReader::create($this->filePath)->getRows()
            ->each(function (array $row) use ($expiresAt, &$totalCount, &$successCount) {
                $totalCount++;
                $email = trim($row['email']);
                $role = $this->hasRoleColumn ? ($row['role'] ?? null) : null;
                $rowValidator = Validator::make(
                    ['email' => $email, 'role' => $role],
                    [
                        'email' => 'required|email|unique:Uneca\Chimera\Models\Invitation,email|unique:Uneca\Chimera\Models\User,email',
                        'role' => 'nullable'
                    ]
                );
                if ($rowValidator->passes()) {
                    $successCount++;
                    $invitation = Invitation::create([
                        'email' => $email,
                        'link' => URL::temporarySignedRoute('register', $expiresAt, ['email' => $email]),
                        'expires_at' => $expiresAt,
                        'role' => $role,
                    ]);
                    if ($this->sendEmails) {
                        Mail::to($invitation->email)->queue(new InvitationMail($invitation));
                    }
                }
            });

        $errors = $successCount < $totalCount ? ' There were some invalid rows with invalid data. Please check your file.' : '';
        Notification::sendNow($this->user, new TaskCompletedNotification(
            'Task completed',
            "$successCount invitations have been created from the $totalCount rows present in the file." . $errors
        ));
    }

    public function failed(\Throwable $exception)
    {
        logger('BulkInvitation Job Failed', ['Exception: ' => $exception->getMessage()]);
        Notification::sendNow($this->user, new TaskFailedNotification(
            'Task failed',
            $exception->getMessage()
        ));
    }
}
