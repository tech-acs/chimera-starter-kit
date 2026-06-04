<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\EditIndicator;

beforeEach(function () {
    Schema::create('indicators', function ($table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug');
        $table->jsonb('title')->nullable();
        $table->jsonb('description')->nullable();
        $table->jsonb('data')->default('[]');
        $table->jsonb('layout')->default('{}');
        $table->string('data_source')->nullable();
        $table->timestamps();
    });

    Schema::create('area_hierarchies', function ($table) {
        $table->id();
        $table->integer('index');
        $table->jsonb('name');
        $table->timestamps();
    });

    Schema::create('inapplicables', function ($table) {
        $table->id();
        $table->foreignId('area_hierarchy_id');
        $table->foreignId('inapplicable_id');
        $table->string('inapplicable_type');
        $table->timestamps();
    });

    DB::table('indicators')->insert([
        'name' => 'test-indicator',
        'slug' => 'test-indicator',
        'title' => '{"en":"Original Title"}',
        'data_source' => 'census_2024',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('rejects edit indicator without id or name', function () {
    $response = DashboardArtefactGenerator::tool(EditIndicator::class, [
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('rejects edit indicator with non-existent id', function () {
    $response = DashboardArtefactGenerator::tool(EditIndicator::class, [
        'id' => 99999,
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('updates indicator title by id', function () {
    $response = DashboardArtefactGenerator::tool(EditIndicator::class, [
        'id' => 1,
        'title' => 'Updated Title',
    ]);

    $response->assertOk();
    $response->assertSee('Updated Title');

    $indicator = DB::table('indicators')->find(1);
    expect($indicator->title)->toBe('{"en":"Updated Title"}');
});

it('updates indicator data_source by name', function () {
    $response = DashboardArtefactGenerator::tool(EditIndicator::class, [
        'name' => 'test-indicator',
        'data_source' => 'survey_2025',
    ]);

    $response->assertOk();

    $indicator = DB::table('indicators')->where('name', 'test-indicator')->first();
    expect($indicator->data_source)->toBe('survey_2025');
});

it('updates indicator with Plotly traces and layout', function () {
    $traces = [
        ['type' => 'bar', 'x' => ['A', 'B'], 'y' => [10, 20], 'name' => 'Series 1'],
    ];
    $layout = ['title' => 'My Chart', 'xaxis' => ['title' => 'Category']];

    $response = DashboardArtefactGenerator::tool(EditIndicator::class, [
        'id' => 1,
        'data' => $traces,
        'layout' => $layout,
    ]);

    $response->assertOk();

    $indicator = DB::table('indicators')->find(1);
    expect(json_decode($indicator->data, true))->toBe($traces);
    expect(json_decode($indicator->layout, true))->toBe($layout);
});
