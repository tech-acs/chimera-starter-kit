<?php

namespace Uneca\Chimera\DTOs;

use Uneca\Chimera\Contracts\ArtefactAttributes;

readonly class MapIndicatorAttributes implements ArtefactAttributes
{
    public function __construct(
        public string $name,
        public string $title,
        public ?string $description,
        public string $dataSource,
        public string $stub
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getStub(): string
    {
        return $this->stub;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'data_source' => $this->dataSource,
        ];
    }
}
