<?php

namespace Uneca\Chimera\Enums;

enum IndicatorScope: string {
    case Pages = 'Pages only';
    case AreaInsights = 'Area insights only';
    case Everywhere = 'Everywhere';
}
