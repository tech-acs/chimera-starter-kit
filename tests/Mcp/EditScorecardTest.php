<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\EditScorecard;

beforeEach(function () {
    Schema::create('scorecards', function ($table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug');
        $table->jsonb('title')->nullable();
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

    DB::table('scorecards')->insert([
        'name' => 'test-scorecard',
        'slug' => 'test-scorecard',
        'title' => '{"en":"Original Title"}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('rejects edit scorecard without id or name', function () {
    $response = DashboardArtefactGenerator::tool(EditScorecard::class, [
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('rejects edit scorecard with non-existent id', function () {
    $response = DashboardArtefactGenerator::tool(EditScorecard::class, [
        'id' => 99999,
        'title' => 'New Title',
    ]);

    $response->assertHasErrors();
});

it('updates scorecard title by id', function () {
    $response = DashboardArtefactGenerator::tool(EditScorecard::class, [
        'id' => 1,
        'title' => 'Updated Title',
    ]);

    $response->assertOk();

    $scorecard = DB::table('scorecards')->find(1);
    expect($scorecard->title)->toBe('{"en":"Updated Title"}');
});

it('updates scorecard data_source by name', function () {
    $response = DashboardArtefactGenerator::tool(EditScorecard::class, [
        'name' => 'test-scorecard',
        'data_source' => 'survey_2025',
    ]);

    $response->assertOk();

    $scorecard = DB::table('scorecards')->where('name', 'test-scorecard')->first();
    expect($scorecard->data_source)->toBe('survey_2025');
});
