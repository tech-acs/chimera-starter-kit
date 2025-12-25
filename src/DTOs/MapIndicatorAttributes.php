<?php

namespace Uneca\Chimera\DTOs;

readonly class MapIndicatorAttributes
{
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public string $dataSource,
        public string $stub
    ) {}

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
