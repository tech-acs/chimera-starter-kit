<?php

use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\CreateMapIndicator;

it('rejects create map indicator without required fields', function () {
    $response = DashboardArtefactGenerator::tool(CreateMapIndicator::class, []);

    $response->assertHasErrors();
});

it('rejects create map indicator with missing name', function () {
    $response = DashboardArtefactGenerator::tool(CreateMapIndicator::class, [
        'title' => 'Test Map',
        'data_source' => 'test_source',
    ]);

    $response->assertHasErrors();
});
