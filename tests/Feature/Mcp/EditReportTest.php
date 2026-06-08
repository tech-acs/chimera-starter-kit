<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\EditReport;

describe('EditReport MCP tool', function () {
    beforeEach(function () {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });

        \DB::table('reports')->insert([
            'name' => 'TestReport',
            'title' => 'Original Title',
            'published' => false,
            'enabled' => false,
        ]);
    });

    afterEach(function () {
        Schema::dropIfExists('reports');
    });

    it('updates report metadata', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditReport::class, [
                'name' => 'TestReport',
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'published' => true,
                'enabled' => true,
            ]);

        $response->assertOk();
        $response->assertSee('Report updated successfully');

        $report = \DB::table('reports')->where('name', 'TestReport')->first();
        expect(json_decode($report->title)->en)->toBe('Updated Title');
        expect(json_decode($report->description)->en)->toBe('Updated description');
        expect($report->published)->toBe(1);
        expect($report->enabled)->toBe(1);
    });

    it('returns error when report not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditReport::class, [
                'name' => 'NonExistent',
                'title' => 'Anything',
            ]);

        $response->assertHasErrors(["Report 'NonExistent' not found"]);
    });
});
