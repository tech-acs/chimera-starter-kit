<?php

namespace Uneca\Chimera\Http\Livewire;

use Livewire\Component;
use Uneca\Chimera\Models\Report;

class SubscribeToReportNotification extends Component
{
    public Report $report;
    public bool $subscribed;

    public function mount()
    {
        $this->user = auth()->user();
        $this->readSubscriptionStatusForCurrentReport();
    }

    private function readSubscriptionStatusForCurrentReport()
    {
        $this->subscribed = auth()->user()->reports->contains($this->report);
    }

    public function toggleSubscription()
    {
        auth()->user()->reports()->toggle($this->report);
        $this->readSubscriptionStatusForCurrentReport();
    }

    public function render()
    {
        return view('chimera::livewire.subscribe-to-report-notification');
    }
}
