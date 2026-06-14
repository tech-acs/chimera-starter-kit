<?php

use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Resources\ArtefactExampleFile;
use Uneca\Chimera\Mcp\Resources\ArtefactExampleIndex;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;

describe('ArtefactExampleIndex resource', function () {
    it('lists references for a valid type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleIndex::class, ['type' => 'scorecard']);

        $response->assertOk();
        $response->assertSee('Available scorecard examples');
        $response->assertSee('TotalPopulation');
        $response->assertSee('FemalePopulation');
        $response->assertSee('AverageHouseholdSize');
    });

    it('returns error for unknown type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleIndex::class, ['type' => 'foobar']);

        $response->assertHasErrors();
        $response->assertSee('Unknown artefact type');
        $response->assertSee('foobar');
    });

    it('strips description from reference files', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleIndex::class, ['type' => 'scorecard']);

        $response->assertOk();
        $response->assertSee('Simple single-aggregate');
    });

    it('lists references for gauge type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleIndex::class, ['type' => 'gauge']);

        $response->assertOk();
        $response->assertSee('Available gauge examples');
        $response->assertSee('Completion');
        $response->assertSee('LiteracyRate');
    });
});

describe('ArtefactExampleFile resource', function () {
    it('returns file contents for valid reference', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleFile::class, [
                'type' => 'scorecard',
                'name' => 'TotalPopulation',
            ]);

        $response->assertOk();
        $response->assertSee('<?php');
        $response->assertSee('ScorecardComponent');
        $response->assertSee('getData');
        $response->assertSee('SUM(total_household_members)');
    });

    it('returns error for unknown type', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleFile::class, [
                'type' => 'foobar',
                'name' => 'TotalPopulation',
            ]);

        $response->assertHasErrors();
        $response->assertSee('Unknown artefact type');
        $response->assertSee('foobar');
    });

    it('returns error for unknown name with available references', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->resource(ArtefactExampleFile::class, [
                'type' => 'scorecard',
                'name' => 'NonExistent',
            ]);

        $response->assertHasErrors();
        $response->assertSee('Example not found');
        $response->assertSee('NonExistent');
        $response->assertSee('TotalPopulation');
        $response->assertSee('FemalePopulation');
    });
});
