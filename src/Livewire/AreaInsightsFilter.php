<?php

namespace Uneca\Chimera\Livewire;

class AreaInsightsFilter extends AreaFilter
{
    public int $removeLastNLevels = 0;

    public string $sessionKey = AreaFilter::AREA_INSIGHTS_SESSION_KEY;

    public string $changeEvent = AreaFilter::AREA_INSIGHTS_CHANGE_EVENT;
}
