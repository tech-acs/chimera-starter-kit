<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\ManagePageAssignment;

describe('ManagePageAssignment MCP tool', function () {
    beforeEach(function () {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('map_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('for')->nullable();
            $table->timestamps();
        });

        Schema::create('pageables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained();
            $table->morphs('pageable');
            $table->integer('rank')->default(0);
            $table->timestamps();
        });

        \DB::table('indicators')->insert(['name' => 'TestIndicator']);
        \DB::table('reports')->insert(['name' => 'TestReport']);
        \DB::table('map_indicators')->insert(['name' => 'TestMapIndicator']);
        \DB::table('pages')->insert(['slug' => 'test-page', 'title' => 'Test Page', 'for' => 'indicators']);
    });

    afterEach(function () {
        Schema::dropIfExists('pageables');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('map_indicators');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('indicators');
    });

    it('attaches an indicator to a page', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ManagePageAssignment::class, [
                'artefact_type' => 'indicator',
                'artefact_name' => 'TestIndicator',
                'page_slug' => 'test-page',
                'rank' => 5,
            ]);

        $response->assertOk();
        $response->assertSee("Indicator 'TestIndicator' attached to page 'test-page' (rank: 5)");

        $this->assertDatabaseHas('pageables', [
            'page_id' => 1,
            'pageable_id' => 1,
            'pageable_type' => 'Uneca\Chimera\Models\Indicator',
            'rank' => 5,
        ]);
    });

    it('detaches an indicator from a page', function () {
        \DB::table('pageables')->insert([
            'page_id' => 1,
            'pageable_id' => 1,
            'pageable_type' => 'Uneca\Chimera\Models\Indicator',
            'rank' => 0,
        ]);

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ManagePageAssignment::class, [
                'artefact_type' => 'indicator',
                'artefact_name' => 'TestIndicator',
                'page_slug' => 'test-page',
                'action' => 'detach',
            ]);

        $response->assertOk();
        $response->assertSee("Indicator 'TestIndicator' detached from page 'test-page'");

        $this->assertDatabaseMissing('pageables', [
            'page_id' => 1,
            'pageable_id' => 1,
            'pageable_type' => 'Uneca\Chimera\Models\Indicator',
        ]);
    });

    it('rejects invalid artefact_type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ManagePageAssignment::class, [
                'artefact_type' => 'scorecard',
                'artefact_name' => 'Anything',
                'page_slug' => 'test-page',
            ]);

        $response->assertHasErrors(['Invalid artefact_type']);
    });

    it('returns error when artefact not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ManagePageAssignment::class, [
                'artefact_type' => 'indicator',
                'artefact_name' => 'NonExistent',
                'page_slug' => 'test-page',
            ]);

        $response->assertHasErrors(["Indicator 'NonExistent' not found"]);
    });

    it('returns error when page not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ManagePageAssignment::class, [
                'artefact_type' => 'indicator',
                'artefact_name' => 'TestIndicator',
                'page_slug' => 'non-existent-page',
            ]);

        $response->assertHasErrors(["Page with slug 'non-existent-page' not found"]);
    });

    it('attaches a report to a page', function () {
        \DB::table('pages')->insert(['slug' => 'report-page', 'title' => 'Report Page', 'for' => 'reports']);

        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ManagePageAssignment::class, [
                'artefact_type' => 'report',
                'artefact_name' => 'TestReport',
                'page_slug' => 'report-page',
            ]);

        $response->assertOk();
        $response->assertSee("Report 'TestReport' attached to page 'report-page'");

        $this->assertDatabaseHas('pageables', [
            'pageable_id' => 1,
            'pageable_type' => 'Uneca\Chimera\Models\Report',
        ]);
    });
});
