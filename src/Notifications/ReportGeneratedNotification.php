<?php

namespace Uneca\Chimera\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Uneca\Chimera\Models\Report;

class ReportGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Report $report)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject("Report ready for download")
                    ->line("A new version of the {$this->report->title} report has been generated")
                    ->action('Download Report', route('report.download', $this->report))
                    ->line('You can download the report from the above link');
    }

    public function toArray($notifiable)
    {
        return [
            'icon' => 'completed',
            'from' => 'Dashboard',
            'title' => "Report ready for download",
            'body' => "A new version of the {$this->report->title} report has been generated. You can download it from the reports page.",
            'sent_at' => Carbon::now(),
        ];
    }
}
