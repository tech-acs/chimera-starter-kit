<?php

use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\CreateIndicator;

it('rejects create indicator without required fields', function () {
    $response = DashboardArtefactGenerator::tool(CreateIndicator::class, []);

    $response->assertHasErrors();
});

it('rejects create indicator with missing name', function () {
    $response = DashboardArtefactGenerator::tool(CreateIndicator::class, [
        'title' => 'Test Indicator',
        'data_source' => 'test_source',
    ]);

    $response->assertHasErrors();
});

it('rejects invalid chart_type', function () {
    $response = DashboardArtefactGenerator::tool(CreateIndicator::class, [
        'name' => 'Test',
        'title' => 'Test Indicator',
        'data_source' => 'test_source',
        'chart_type' => 'invalid_type',
    ]);

    $response->assertHasErrors();
    $response->assertSee('Invalid chart_type');
});
