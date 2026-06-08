<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\EditGauge;

describe('EditGauge MCP tool', function () {
    beforeEach(function () {
        Schema::create('gauges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        \DB::table('gauges')->insert([
            'name' => 'TestGauge',
            'title' => 'Original Title',
            'subtitle' => 'Original subtitle',
            'published' => false,
        ]);
    });

    afterEach(function () {
        Schema::dropIfExists('gauges');
    });

    it('updates gauge metadata', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditGauge::class, [
                'name' => 'TestGauge',
                'title' => 'Updated Title',
                'subtitle' => 'Updated subtitle',
                'description' => 'Updated description',
                'published' => true,
            ]);

        $response->assertOk();
        $response->assertSee('Gauge updated successfully');

        $gauge = \DB::table('gauges')->where('name', 'TestGauge')->first();
        expect(json_decode($gauge->title)->en)->toBe('Updated Title');
        expect(json_decode($gauge->subtitle)->en)->toBe('Updated subtitle');
        expect($gauge->description)->toBe('Updated description');
        expect($gauge->published)->toBe(1);
    });

    it('returns error when gauge not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditGauge::class, [
                'name' => 'NonExistent',
                'title' => 'Anything',
            ]);

        $response->assertHasErrors(["Gauge 'NonExistent' not found"]);
    });
});
