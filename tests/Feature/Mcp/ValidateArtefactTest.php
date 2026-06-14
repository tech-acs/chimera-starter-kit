<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\ValidateArtefact;

describe('ValidateArtefact MCP tool', function () {
    beforeEach(function () {
        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->nullable();
            $table->text('title')->nullable();
            $table->string('data_source')->nullable();
            $table->boolean('published')->default(false);
            $table->string('scope')->nullable();
            $table->timestamps();
        });
    });

    afterEach(function () {
        Schema::dropIfExists('scorecards');
    });

    it('returns error when type is missing', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ValidateArtefact::class, ['name' => 'Anything']);

        $response->assertHasErrors(['type is required']);
    });

    it('returns error when name is missing', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ValidateArtefact::class, ['type' => 'scorecard']);

        $response->assertHasErrors(['name is required']);
    });

    it('returns error for invalid type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ValidateArtefact::class, ['type' => 'widget', 'name' => 'Test']);

        $response->assertHasErrors(["Invalid type 'widget'"]);

        $response2 = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ValidateArtefact::class, ['type' => 'chart', 'name' => 'Test']);

        $response2->assertHasErrors(["Invalid type 'chart'"]);
    });

    it('returns error when artefact is not found', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ValidateArtefact::class, ['type' => 'scorecard', 'name' => 'NonExistent']);

        $response->assertHasErrors(["scorecard with name 'NonExistent' not found"]);
    });
});
