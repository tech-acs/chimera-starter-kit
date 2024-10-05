<?php

namespace Uneca\Chimera\Services;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class XRayLogFormatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                '%message%', null, true, true
            ));
        }
    }
}
