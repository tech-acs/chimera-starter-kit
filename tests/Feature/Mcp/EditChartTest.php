<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\EditChart;

function editChartCreateIndicatorsTable(): void
{
    Schema::create('indicators', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug')->nullable();
        $table->string('title')->nullable();
        $table->text('description')->nullable();
        $table->string('type')->default('bar');
        $table->text('data')->nullable();
        $table->text('layout')->nullable();
        $table->string('data_source')->nullable();
        $table->boolean('published')->default(false);
        $table->string('scope')->nullable();
        $table->timestamps();
    });
}

function editChartRegisterTestClass(string $name, string $dataSource = 'households'): void
{
    $parts = explode('/', $name);
    $className = array_pop($parts);
    $namespace = 'App\\Livewire\\Indicator' . (count($parts) > 0 ? '\\' . implode('\\', $parts) : '');
    eval("
        namespace {$namespace} {
            class {$className} extends \\Uneca\\Chimera\\Livewire\\Chart {
                public function getData(string \$filterPath): \\Illuminate\\Support\\Collection {
                    return collect([
                        (object) ['area_name' => 'County A', 'average_age' => 35.2],
                        (object) ['area_name' => 'County B', 'average_age' => 42.7],
                        (object) ['area_name' => 'County C', 'average_age' => 28.9],
                    ]);
                }
            }
        }
    ");
}

describe('EditChart MCP tool', function () {
    beforeEach(function () {
        editChartCreateIndicatorsTable();
    });

    afterEach(function () {
        Schema::dropIfExists('indicators');
    });

    it('saves traces and layout for an indicator', function () {
        \DB::table('indicators')->insert([
            'name' => 'Households/AverageAgeByArea',
            'slug' => 'households.average-age-by-area',
            'title' => 'Average Age by Area',
            'type' => 'bar',
            'data_source' => 'households',
            'data' => '[]',
            'layout' => '{}',
        ]);

        editChartRegisterTestClass('Households/AverageAgeByArea');

        $traces = [
            [
                'type' => 'bar',
                'meta' => ['columnNames' => ['x' => 'area_name', 'y' => ['average_age']]],
                'name' => 'Average Age',
                'hovertemplate' => '%{y:.1f} years',
            ],
        ];

        $layout = [
            'xaxis' => ['title' => ['text' => 'County']],
            'yaxis' => ['title' => ['text' => 'Average Age']],
        ];

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'Households/AverageAgeByArea',
                'data' => $traces,
                'layout' => $layout,
            ]);

        $response->assertOk();
        $response->assertSee('Chart designed successfully');
        $response->assertSee('average_age');
        $response->assertSee('area_name');

        $indicator = \DB::table('indicators')->where('name', 'Households/AverageAgeByArea')->first();
        $savedData = json_decode($indicator->data, true);
        expect($savedData)->toBeArray();
        expect($savedData[0]['type'])->toBe('bar');
        expect($savedData[0]['meta']['columnNames']['x'])->toBe('area_name');
        expect($savedData[0]['meta']['columnNames']['y'])->toBe(['average_age']);

        $savedLayout = json_decode($indicator->layout, true);
        expect($savedLayout['xaxis']['title']['text'])->toBe('County');
        expect($savedLayout['yaxis']['title']['text'])->toBe('Average Age');
    });

    it('saves traces without layout (uses existing)', function () {
        \DB::table('indicators')->insert([
            'name' => 'SimpleIndicator',
            'slug' => 'simple-indicator',
            'title' => 'Simple',
            'type' => 'bar',
            'data_source' => 'households',
            'data' => '[]',
            'layout' => json_encode(['showlegend' => false]),
        ]);

        editChartRegisterTestClass('SimpleIndicator');

        $traces = [
            [
                'type' => 'bar',
                'meta' => ['columnNames' => ['x' => 'area_name', 'y' => ['average_age']]],
                'name' => 'Simple',
            ],
        ];

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'SimpleIndicator',
                'data' => $traces,
            ]);

        $response->assertOk();

        $indicator = \DB::table('indicators')->where('name', 'SimpleIndicator')->first();
        $savedData = json_decode($indicator->data, true);
        expect($savedData[0]['type'])->toBe('bar');

        // Layout should be unchanged
        $savedLayout = json_decode($indicator->layout, true);
        expect($savedLayout['showlegend'])->toBeFalse();
    });

    it('returns error when indicator has no data (getData returns empty)', function () {
        \DB::table('indicators')->insert([
            'name' => 'EmptyIndicator',
            'slug' => 'empty-indicator',
            'title' => 'Empty',
            'type' => 'bar',
            'data_source' => 'households',
            'data' => '[]',
        ]);

        $className = 'EmptyIndicator';
        eval("
            namespace App\\Livewire\\Indicator {
                class {$className} extends \\Uneca\\Chimera\\Livewire\\Chart {
                    public function getData(string \$filterPath): \\Illuminate\\Support\\Collection {
                        return collect();
                    }
                }
            }
        ");

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'EmptyIndicator',
                'data' => [
                    ['type' => 'bar', 'meta' => ['columnNames' => ['x' => 'area_name', 'y' => ['value']]], 'name' => 'Test'],
                ],
            ]);

        $response->assertHasErrors(["getData() returned no rows"]);
    });

    it('returns error when columnNames reference non-existent columns', function () {
        \DB::table('indicators')->insert([
            'name' => 'MismatchIndicator',
            'slug' => 'mismatch-indicator',
            'title' => 'Mismatch',
            'type' => 'bar',
            'data_source' => 'households',
            'data' => '[]',
        ]);

        editChartRegisterTestClass('MismatchIndicator');

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'MismatchIndicator',
                'data' => [
                    [
                        'type' => 'bar',
                        'meta' => ['columnNames' => ['x' => 'area_name', 'y' => ['nonexistent_column']]],
                        'name' => 'Test',
                    ],
                ],
            ]);

        $response->assertHasErrors(['nonexistent_column']);
    });

    it('returns error when indicator not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'NonExistent',
                'data' => [
                    ['type' => 'bar', 'meta' => ['columnNames' => ['x' => 'area_name', 'y' => ['value']]], 'name' => 'Test'],
                ],
            ]);

        $response->assertHasErrors(["Indicator 'NonExistent' not found"]);
    });

    it('returns error when data parameter is empty', function () {
        \DB::table('indicators')->insert([
            'name' => 'TestIndicator',
            'slug' => 'test-indicator',
            'title' => 'Test',
            'type' => 'bar',
            'data_source' => 'households',
            'data' => '[]',
        ]);

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'TestIndicator',
                'data' => [],
            ]);

        $response->assertHasErrors(['non-empty array']);
    });

    it('returns error when name is missing', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'data' => [
                    ['type' => 'bar', 'meta' => ['columnNames' => ['y' => ['value']]], 'name' => 'Test'],
                ],
            ]);

        $response->assertHasErrors(['name']);
    });

    it('returns error when trace is missing meta.columnNames', function () {
        \DB::table('indicators')->insert([
            'name' => 'NoColumnNames',
            'slug' => 'no-column-names',
            'title' => 'No Cols',
            'type' => 'bar',
            'data_source' => 'households',
            'data' => '[]',
        ]);

        editChartRegisterTestClass('NoColumnNames');

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditChart::class, [
                'name' => 'NoColumnNames',
                'data' => [
                    ['type' => 'bar', 'name' => 'Test'],
                ],
            ]);

        $response->assertHasErrors(['meta.columnNames']);
    });
});
