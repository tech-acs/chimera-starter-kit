<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\EditMapIndicator;

describe('EditMapIndicator MCP tool', function () {
    beforeEach(function () {
        Schema::create('map_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        \DB::table('map_indicators')->insert([
            'name' => 'TestMapIndicator',
            'title' => 'Original Title',
            'published' => false,
        ]);
    });

    afterEach(function () {
        Schema::dropIfExists('map_indicators');
    });

    it('updates map indicator metadata', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditMapIndicator::class, [
                'name' => 'TestMapIndicator',
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'published' => true,
            ]);

        $response->assertOk();
        $response->assertSee('Map indicator updated successfully');

        $mi = \DB::table('map_indicators')->where('name', 'TestMapIndicator')->first();
        expect(json_decode($mi->title)->en)->toBe('Updated Title');
        expect(json_decode($mi->description)->en)->toBe('Updated description');
        expect($mi->published)->toBe(1);
    });

    it('returns error when map indicator not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditMapIndicator::class, [
                'name' => 'NonExistent',
                'title' => 'Anything',
            ]);

        $response->assertHasErrors(["Map indicator 'NonExistent' not found"]);
    });
});
