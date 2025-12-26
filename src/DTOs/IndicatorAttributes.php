<?php

namespace Uneca\Chimera\DTOs;

readonly class IndicatorAttributes
{
    public function __construct(
        public string $name,
        public string $title,
        public string $dataSource,
        public string $type,
        public ?string $description = null,
        public array $data = [],
        public array $layout = [],
        public string $stub
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'data_source' => $this->dataSource,
            'type' => $this->type,
            'data' => $this->data,
            'layout' => $this->layout,
        ];
    }
}
