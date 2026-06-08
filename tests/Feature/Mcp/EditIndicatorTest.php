<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\EditIndicator;

describe('EditIndicator MCP tool', function () {
    beforeEach(function () {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('help')->nullable();
            $table->text('data')->nullable();
            $table->text('layout')->nullable();
            $table->boolean('published')->default(false);
            $table->string('scope')->nullable();
            $table->timestamps();
        });

        \DB::table('indicators')->insert([
            'name' => 'TestIndicator',
            'title' => 'Original Title',
            'description' => 'Original description',
            'help' => 'Original help',
            'published' => false,
            'scope' => 'Everywhere',
        ]);
    });

    afterEach(function () {
        Schema::dropIfExists('indicators');
    });

    it('updates indicator metadata', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditIndicator::class, [
                'name' => 'TestIndicator',
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'help' => 'Updated help',
                'published' => true,
                'scope' => 'Pages only',
            ]);

        $response->assertOk();
        $response->assertSee('Indicator updated successfully');

        $indicator = \DB::table('indicators')->where('name', 'TestIndicator')->first();
        expect(json_decode($indicator->title)->en)->toBe('Updated Title');
        expect(json_decode($indicator->description)->en)->toBe('Updated description');
        expect(json_decode($indicator->help)->en)->toBe('Updated help');
        expect($indicator->published)->toBe(1);
        expect($indicator->scope)->toBe('Pages only');
    });

    it('updates only provided fields', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditIndicator::class, [
                'name' => 'TestIndicator',
                'title' => 'Only Title Changed',
            ]);

        $response->assertOk();

        $indicator = \DB::table('indicators')->where('name', 'TestIndicator')->first();
        expect(json_decode($indicator->title)->en)->toBe('Only Title Changed');
        expect($indicator->description)->toBe('Original description');
        expect($indicator->published)->toBe(0);
    });

    it('returns error when indicator not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditIndicator::class, [
                'name' => 'NonExistent',
                'title' => 'Anything',
            ]);

        $response->assertHasErrors(["Indicator 'NonExistent' not found"]);
    });

    it('rejects invalid scope value', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(EditIndicator::class, [
                'name' => 'TestIndicator',
                'scope' => 'invalid_scope',
            ]);

        $response->assertHasErrors(['Invalid scope']);
    });
});
