<?php

namespace Uneca\Chimera\Services;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class SmartTableData
{
    public Builder $builder;
    public Request $request;
    public Collection $columns;
    public Collection $sortableColumns;
    public LengthAwarePaginator $rows;
    public Collection $searchableColumns;
    public string $searchPlaceholder;
    public ?string $searchHint;
    public string $sortBy;
    public string $sortDirection = 'ASC';
    public int $defaultPageSize;

    public function __construct(Builder $builder, Request $request)
    {
        // ToDo: Don't reverse sort direction for refresh
        $this->builder = $builder;
        $this->request = $request;
        $this->searchableColumns = collect();
        $this->searchPlaceholder = __('Search');
        $this->defaultPageSize = config('chimera.records_per_page');
        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = collect($columns)->map(function ($column) {
            $column->belongsTo($this);
            return $column;
        });
        $this->sortableColumns = $this->columns
            ->filter(fn ($c) => $c->isSortable())
            ->map(fn ($c) => $c->attribute);
        return $this;
    }

    public function sortBy(string $column): self
    {

        if ($this->request->has('sort_by') && $this->sortableColumns->contains($this->request->get('sort_by'))) {
            $this->sortBy = $this->request->get('sort_by');
            return $this;
        }
        $this->sortBy = $column;
        return $this;
    }

    public function reverseSortDirection(string $dir): void
    {
        //$this->sortDirection = $dir === 'ASC' ? 'DESC' : 'ASC';
    }

    public function searchable(array $columns, $searchHint = null): self
    {
        $this->searchableColumns = collect($columns);
        $this->searchHint = $searchHint;
        if (is_null($searchHint) && $this->searchableColumns->isNotEmpty()) {
            $this->searchHint = "Search by " .
                $this->searchableColumns
                    ->map(fn ($col) => str($col)->replace('_', ' '))
                    ->join(', ', ' or ');
        }
        return $this;
    }

    public function build(): self
    {
        if (! isset($this->sortBy)) {
            dd('You have not set a default sorting column');
        }
        if ($this->request->has('sort_by') && $this->sortableColumns->contains($this->request->get('sort_by'))) {
            //list($col, $dir) = Session::get('previous-sorting', [$this->request->get('sort_by'), 'ASC']);
            /*if ($col === $this->request->get('sort_by')) {
                $this->reverseSortDirection($dir);
            } else {*/
                $this->sortBy = $this->request->get('sort_by');
                $this->sortDirection = 'ASC';
            //}
        }
        if ($this->request->has('page_size')) {
            Session::put('page_size', $this->request->get('page_size'));
        }
        $this->rows = $this->builder
            ->when($this->request->has('search'), function ($query) {
                return $query->whereAny($this->searchableColumns->toArray(), 'ILIKE', '%' . $this->request->get('search') . '%');
            })
            ->when(isset($this->sortBy), function ($query) {
                return $query->orderBy($this->sortBy, $this->sortDirection);
            })
            ->paginate(Session::get('page_size', $this->request->get('page_size', $this->defaultPageSize)));
        //Session::put('previous-sorting', [$this->sortBy ?? null, $this->sortDirection]);
        return $this;
    }
}
