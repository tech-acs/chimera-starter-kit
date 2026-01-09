<?php

namespace Uneca\Chimera\Enums;

enum ScorecardScope: string {
    case Dashboard = 'Dashboard only';
    case AreaInsights = 'Area insights only';
    case Everywhere = 'Everywhere';
}
