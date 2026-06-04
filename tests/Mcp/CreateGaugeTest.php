<?php

use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\CreateGauge;

it('rejects create gauge without required fields', function () {
    $response = DashboardArtefactGenerator::tool(CreateGauge::class, []);

    $response->assertHasErrors();
});

it('rejects create gauge with missing name', function () {
    $response = DashboardArtefactGenerator::tool(CreateGauge::class, [
        'title' => 'Test Gauge',
        'data_source' => 'test_source',
    ]);

    $response->assertHasErrors();
});
