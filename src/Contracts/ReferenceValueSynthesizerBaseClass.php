<?php

namespace Uneca\Chimera\Contracts;

use Illuminate\Support\Collection;

abstract class ReferenceValueSynthesizerBaseClass
{
    public string $dataSource;
    public string $indicator;
    public int $level;
    public bool $isAdditive;

    abstract public function getData(string $dataSource, string $path): Collection;
}
