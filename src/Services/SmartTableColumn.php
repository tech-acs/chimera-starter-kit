<?php

namespace Uneca\Chimera\Services;

use Illuminate\Support\Facades\Blade;
use Uneca\Chimera\Enums\SortDirection;

class SmartTableColumn
{
    private SmartTableData $table;
    private ?string $label = null;
    private bool $isSortable = false;
    private SortDirection $sortDirection = SortDirection::ASC;
    private string $bladeTemplate;
    public string $attribute;
    public string $classes = '';

    public function __construct(string $attribute)
    {
        $this->attribute = $attribute;
        $this->bladeTemplate = <<<'blade'
            {{ $row[$column->attribute] }}
        blade;
    }

    public static function make(string $attribute)
    {
        return app(static::class, ['attribute' => $attribute]);
    }

    public function belongsTo(SmartTableData $smartTableData)
    {
        $this->table = $smartTableData;
        if ($this->table->request->get('sort_by') == $this->attribute) {
            $this->sortDirection = $this->table->request->enum('sort_direction', SortDirection::class) ?? SortDirection::ASC;
        }
    }

    protected function applyLabelFormatting(string $label): string
    {
        return str($label)->upper();
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel(): string
    {
        if (is_null($this->label)) {
            $this->label = $this->attribute;
        }
        return $this->applyLabelFormatting($this->label);
    }

    public function sortable(): self
    {
        $this->isSortable = true;
        return $this;
    }

    public function tdClasses(string $classes): self
    {
        $this->classes = $classes;
        return $this;
    }

    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    public function reverseSortDirection(): string
    {
        return $this->table->sortBy == $this->attribute ?
            ($this->sortDirection == SortDirection::ASC ? 'DESC' : 'ASC') :
            'ASC';
    }

    public function sortIcon(): string
    {
        if ($this->isSortable) {
            if ($this->table->sortBy === $this->attribute) {
                if ($this->table->sortDirection === SortDirection::ASC) {
                    return Blade::render('<x-chimera::icon.sort-asc class="text-blue-600" />');
                } else {
                    return Blade::render('<x-chimera::icon.sort-desc class="text-blue-600" />');
                }
            }
            return Blade::render('<x-chimera::icon.sort-asc />');
        }
        return '';
    }

    public function setBladeTemplate(string $bladeTemplate): self
    {
        $this->bladeTemplate = $bladeTemplate;
        return $this;
    }

    public function getBladeTemplate()
    {
        return $this->bladeTemplate;
    }
}
