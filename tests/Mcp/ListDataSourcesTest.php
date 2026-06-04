<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\ListDataSources;

it('returns message when data_sources table does not exist', function () {
    Schema::dropIfExists('data_sources');

    $response = DashboardArtefactGenerator::tool(ListDataSources::class, []);

    $response->assertSee('does not exist');
});

it('returns message when data_sources table is empty', function () {
    Schema::dropIfExists('data_sources');
    Schema::create('data_sources', function ($table) {
        $table->id();
        $table->string('name');
        $table->json('title')->nullable();
        $table->boolean('connection_active')->default(true);
        $table->timestamp('start_date')->nullable();
        $table->timestamp('end_date')->nullable();
        $table->integer('rank')->nullable();
    });

    $response = DashboardArtefactGenerator::tool(ListDataSources::class, []);

    $response->assertSee('is empty');
});

it('lists available data sources', function () {
    Schema::dropIfExists('data_sources');
    Schema::create('data_sources', function ($table) {
        $table->id();
        $table->string('name');
        $table->json('title')->nullable();
        $table->boolean('connection_active')->default(true);
        $table->timestamp('start_date')->nullable();
        $table->timestamp('end_date')->nullable();
        $table->integer('rank')->nullable();
    });

    DB::table('data_sources')->insert([
        'name' => 'households',
        'title' => json_encode(['en' => 'Households']),
        'connection_active' => true,
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'rank' => 1,
    ]);

    DB::table('data_sources')->insert([
        'name' => 'census_2025',
        'title' => json_encode(['en' => 'Census 2025']),
        'connection_active' => false,
        'start_date' => '2025-08-01',
        'end_date' => '2025-08-31',
        'rank' => 2,
    ]);

    $response = DashboardArtefactGenerator::tool(ListDataSources::class, []);

    $response->assertSee(['households', 'Households', 'active', 'census_2025', 'Census 2025', 'inactive']);
});
