<?php

namespace Uneca\Chimera\Contracts;

interface ArtefactAttributes
{
    public function toArray(): array;

    public function getName(): string;

    public function getStub(): string;
}
