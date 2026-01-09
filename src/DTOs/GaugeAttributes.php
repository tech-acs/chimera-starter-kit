<?php

namespace Uneca\Chimera\DTOs;

readonly class GaugeAttributes
{
    public function __construct(
        public string $name,
        public string $title,
        public string $subtitle,
        public string $dataSource,
        public string $stub
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'data_source' => $this->dataSource,
        ];
    }
}
