<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\EditMapIndicator;

beforeEach(function () {
    Schema::create('map_indicators', function ($table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug');
        $table->jsonb('title')->nullable();
        $table->jsonb('description')->nullable();
        $table->string('data_source')->nullable();
        $table->timestamps();
    });

    DB::table('map_indicators')->insert([
        'name' => 'test-map-indicator',
        'slug' => 'test-map-indicator',
        'title' => '{"en":"Original Title"}',
        'data_source' => 'census_2024',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('rejects edit map indicator without id or name', function () {
    $response = DashboardArtefactGenerator::tool(EditMapIndicator::class, [
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('rejects edit map indicator with non-existent id', function () {
    $response = DashboardArtefactGenerator::tool(EditMapIndicator::class, [
        'id' => 99999,
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('updates map indicator title by id', function () {
    $response = DashboardArtefactGenerator::tool(EditMapIndicator::class, [
        'id' => 1,
        'title' => 'Updated Title',
    ]);

    $response->assertOk();

    $mapIndicator = DB::table('map_indicators')->find(1);
    expect($mapIndicator->title)->toBe('{"en":"Updated Title"}');
});

it('updates map indicator description and data_source by name', function () {
    $response = DashboardArtefactGenerator::tool(EditMapIndicator::class, [
        'name' => 'test-map-indicator',
        'description' => 'Updated description',
        'data_source' => 'survey_2025',
    ]);

    $response->assertOk();

    $mapIndicator = DB::table('map_indicators')->where('name', 'test-map-indicator')->first();
    expect($mapIndicator->description)->toBe('{"en":"Updated description"}');
    expect($mapIndicator->data_source)->toBe('survey_2025');
});
