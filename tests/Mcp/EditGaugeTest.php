<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\EditGauge;

beforeEach(function () {
    Schema::create('gauges', function ($table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug');
        $table->jsonb('title')->nullable();
        $table->jsonb('subtitle')->nullable();
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

    DB::table('gauges')->insert([
        'name' => 'test-gauge',
        'slug' => 'test-gauge',
        'title' => '{"en":"Original Title"}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('rejects edit gauge without id or name', function () {
    $response = DashboardArtefactGenerator::tool(EditGauge::class, [
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('rejects edit gauge with non-existent id', function () {
    $response = DashboardArtefactGenerator::tool(EditGauge::class, [
        'id' => 99999,
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('updates gauge title by id', function () {
    $response = DashboardArtefactGenerator::tool(EditGauge::class, [
        'id' => 1,
        'title' => 'Updated Title',
    ]);

    $response->assertOk();

    $gauge = DB::table('gauges')->find(1);
    expect($gauge->title)->toBe('{"en":"Updated Title"}');
});

it('updates gauge subtitle and data_source by name', function () {
    $response = DashboardArtefactGenerator::tool(EditGauge::class, [
        'name' => 'test-gauge',
        'subtitle' => 'Updated Subtitle',
        'data_source' => 'survey_2025',
    ]);

    $response->assertOk();

    $gauge = DB::table('gauges')->where('name', 'test-gauge')->first();
    expect($gauge->subtitle)->toBe('{"en":"Updated Subtitle"}');
    expect($gauge->data_source)->toBe('survey_2025');
});
