<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\EditReport;

beforeEach(function () {
    Schema::create('reports', function ($table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug');
        $table->jsonb('title')->nullable();
        $table->jsonb('description')->nullable();
        $table->string('data_source')->nullable();
        $table->boolean('enabled')->default(false);
        $table->timestamps();
    });

    DB::table('reports')->insert([
        'name' => 'test-report',
        'slug' => 'test-report',
        'title' => '{"en":"Original Title"}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('rejects edit report without id or name', function () {
    $response = DashboardArtefactGenerator::tool(EditReport::class, [
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('rejects edit report with non-existent id', function () {
    $response = DashboardArtefactGenerator::tool(EditReport::class, [
        'id' => 99999,
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('updates report title by id', function () {
    $response = DashboardArtefactGenerator::tool(EditReport::class, [
        'id' => 1,
        'title' => 'Updated Title',
    ]);

    $response->assertOk();

    $report = DB::table('reports')->find(1);
    expect($report->title)->toBe('{"en":"Updated Title"}');
});

it('updates report description and data_source by name', function () {
    $response = DashboardArtefactGenerator::tool(EditReport::class, [
        'name' => 'test-report',
        'description' => 'Updated description',
        'data_source' => 'survey_2025',
    ]);

    $response->assertOk();

    $report = DB::table('reports')->where('name', 'test-report')->first();
    expect($report->description)->toBe('{"en":"Updated description"}');
    expect($report->data_source)->toBe('survey_2025');
});

it('updates report enabled flag', function () {
    $response = DashboardArtefactGenerator::tool(EditReport::class, [
        'id' => 1,
        'enabled' => true,
    ]);

    $response->assertOk();

    $report = DB::table('reports')->find(1);
    expect((bool) $report->enabled)->toBeTrue();
});
