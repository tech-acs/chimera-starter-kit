<?php

use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\StagePresetPack;

describe('StagePresetPack tool', function () {
    it('returns artefacts for a valid pack', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(StagePresetPack::class, ['pack' => 'census-enumeration']);

        $response->assertOk();
        $response->assertSee('census-enumeration');
        $response->assertSee('PopulationCount');
        $response->assertSee('NumberOfHouseholds');
        $response->assertSee('scorecard');
        $response->assertSee('indicator');
        $response->assertSee('report');
        $response->assertSee('Total enumerated population across all enumeration areas');
    });

    it('includes body/hints for each artefact', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(StagePresetPack::class, ['pack' => 'census-enumeration']);

        $response->assertOk();
        $response->assertSee('COUNT(*)');
        $response->assertSee('area_code');
    });

    it('returns error for unknown pack', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(StagePresetPack::class, ['pack' => 'nonexistent']);

        $response->assertHasErrors(['not found']);
    });

    it('returns error when pack parameter is missing', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(StagePresetPack::class, []);

        $response->assertHasErrors(["'pack' parameter is required"]);
    });
});
