<?php

use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\CreateReport;

it('rejects create report without required fields', function () {
    $response = DashboardArtefactGenerator::tool(CreateReport::class, []);

    $response->assertHasErrors();
});

it('rejects create report with missing name', function () {
    $response = DashboardArtefactGenerator::tool(CreateReport::class, [
        'title' => 'Test Report',
        'data_source' => 'test_source',
    ]);

    $response->assertHasErrors();
});
