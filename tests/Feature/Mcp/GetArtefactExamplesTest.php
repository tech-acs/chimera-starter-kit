<?php

use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Services\ArtefactExampleService;
use Uneca\Chimera\Mcp\Tools\GetArtefactExamples;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;

describe('ArtefactExampleService', function () {
    it('validates known types', function () {
        $service = app(ArtefactExampleService::class);

        expect($service->isValidType('scorecard'))->toBeTrue();
        expect($service->isValidType('gauge'))->toBeTrue();
        expect($service->isValidType('indicator'))->toBeTrue();
        expect($service->isValidType('map-indicator'))->toBeTrue();
        expect($service->isValidType('report'))->toBeTrue();
        expect($service->isValidType('foobar'))->toBeFalse();
    });

    it('lists examples for a type', function () {
        $service = app(ArtefactExampleService::class);
        $examples = $service->listExamples('scorecard');

        expect($examples)->toBeArray();
        expect($examples)->not->toBeEmpty();
        expect($examples[0])->toHaveKey('name');
        expect($examples[0])->toHaveKey('description');
    });

    it('returns example content by type and name', function () {
        $service = app(ArtefactExampleService::class);
        $content = $service->getExample('scorecard', 'TotalPopulation');

        expect($content)->toBeString();
        expect($content)->toContain('<?php');
        expect($content)->toContain('getData');
    });

    it('returns null for missing example', function () {
        $service = app(ArtefactExampleService::class);

        expect($service->getExample('scorecard', 'NonExistent'))->toBeNull();
    });

    it('returns empty list for unknown type', function () {
        $service = app(ArtefactExampleService::class);

        expect($service->listExamples('foobar'))->toBe([]);
    });
});

describe('GetArtefactExamples tool (list mode)', function () {
    it('lists references for a valid type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetArtefactExamples::class, ['type' => 'scorecard']);

        $response->assertOk();
        $response->assertSee('Available scorecard examples');
        $response->assertSee('TotalPopulation');
        $response->assertSee('FemalePopulation');
        $response->assertSee('AverageHouseholdSize');
    });

    it('includes descriptions in listing', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetArtefactExamples::class, ['type' => 'scorecard']);

        $response->assertOk();
        $response->assertSee('Simple single-aggregate');
    });

    it('lists references for gauge type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetArtefactExamples::class, ['type' => 'gauge']);

        $response->assertOk();
        $response->assertSee('Completion');
        $response->assertSee('LiteracyRate');
    });

    it('returns error for unknown type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetArtefactExamples::class, ['type' => 'foobar']);

        $response->assertOk();
        $response->assertSee('Unknown artefact type');
        $response->assertSee('foobar');
    });
});

describe('GetArtefactExamples tool (file mode)', function () {
    it('returns file contents for valid reference', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetArtefactExamples::class, [
                'type' => 'scorecard',
                'name' => 'TotalPopulation',
            ]);

        $response->assertOk();
        $response->assertSee('<?php');
        $response->assertSee('getData');
        $response->assertSee('SUM(total_household_members)');
    });

    it('returns error for unknown name with available references', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetArtefactExamples::class, [
                'type' => 'scorecard',
                'name' => 'NonExistent',
            ]);

        $response->assertOk();
        $response->assertSee('Example not found');
        $response->assertSee('NonExistent');
        $response->assertSee('TotalPopulation');
        $response->assertSee('FemalePopulation');
    });
});
