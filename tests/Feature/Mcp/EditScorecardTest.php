<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\EditScorecard;

describe('EditScorecard MCP tool', function () {
    beforeEach(function () {
        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->string('scope')->nullable();
            $table->timestamps();
        });

        \DB::table('scorecards')->insert([
            'name' => 'TestScorecard',
            'title' => 'Original Title',
            'published' => false,
            'scope' => 'national',
        ]);
    });

    afterEach(function () {
        Schema::dropIfExists('scorecards');
    });

    it('updates scorecard metadata', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditScorecard::class, [
                'name' => 'TestScorecard',
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'published' => true,
                'scope' => 'Dashboard only',
            ]);

        $response->assertOk();
        $response->assertSee('Scorecard updated successfully');

        $scorecard = \DB::table('scorecards')->where('name', 'TestScorecard')->first();
        expect(json_decode($scorecard->title)->en)->toBe('Updated Title');
        expect($scorecard->description)->toBe('Updated description');
        expect($scorecard->published)->toBe(1);
        expect($scorecard->scope)->toBe('Dashboard only');
    });

    it('returns error when scorecard not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditScorecard::class, [
                'name' => 'NonExistent',
                'title' => 'Anything',
            ]);

        $response->assertHasErrors(["Scorecard 'NonExistent' not found"]);
    });
});
