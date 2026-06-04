<?php

use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\CreateScorecard;

it('rejects create scorecard without required fields', function () {
    $response = DashboardArtefactGenerator::tool(CreateScorecard::class, []);

    $response->assertHasErrors();
});

it('rejects create scorecard with missing name', function () {
    $response = DashboardArtefactGenerator::tool(CreateScorecard::class, [
        'title' => 'Test Scorecard',
        'data_source' => 'test_source',
    ]);

    $response->assertHasErrors();
});
