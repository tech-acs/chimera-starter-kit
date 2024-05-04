<?php

namespace Uneca\Chimera\Services;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Uneca\Chimera\Enums\SortDirection;
use Spatie\SimpleExcel\SimpleExcelWriter;

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
    public bool $isDownloadable = false;
    public string $downloadLink;
    public string $sortBy;
    public SortDirection $sortDirection = SortDirection::ASC;
    public int $defaultPageSize;

    public function __construct(Builder $builder, Request $request)
    {
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

    public function downloadable()
    {
        $router = app()->make(Router::class);
        $router->pushMiddlewareToGroup('web', \Uneca\Chimera\Http\Middleware\DownloadSmartTableData::class);

        $this->isDownloadable = true;
        /*$router = app()->make(Router::class);
        $router->addRoute('get', 'download', function () {
            //return response()->abort(404);
        })->name('download_csv');*/
        return $this;
    }

    public function build(): self
    {
        if (! isset($this->sortBy)) {
            dd('You have not set a default sorting column');
        }
        if ($this->request->has('sort_by') && $this->sortableColumns->contains($this->request->get('sort_by'))) {
            $this->sortBy = $this->request->get('sort_by');
            $this->sortDirection = $this->request->enum('sort_direction', SortDirection::class) ?? SortDirection::ASC;
        }
        if ($this->request->has('page_size')) {
            Session::put('page_size', $this->request->get('page_size'));
        }
        $this->builder
            ->when($this->request->has('search'), function ($query) {
                return $query->whereAny($this->searchableColumns->toArray(), 'ILIKE', '%' . $this->request->get('search') . '%');
            })
            ->when(isset($this->sortBy), function ($query) {
                return $query->orderBy($this->sortBy, $this->sortDirection->value);
            });
        $this->rows = $this->builder
            ->paginate(Session::get('page_size', $this->request->get('page_size', $this->defaultPageSize)));

        if ($this->isDownloadable) {
            $path = storage_path('app/public/test.csv');
            $data = $this->builder->get()->select($this->columns->pluck('attribute')->toArray());
            //dd($this->builder->get(), $data, $this->columns->pluck('attribute')->toArray());
            /*$writer = SimpleExcelWriter::create($path);
            foreach ($data as $record) {
                $writer->addRow($record);
            }*/
            $this->downloadLink = url('storage/test.csv');
        }
        return $this;
    }
}
