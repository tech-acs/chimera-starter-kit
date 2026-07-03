<?php

use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Services\PresetPackService;
use Uneca\Chimera\Mcp\Tools\GetPresetPacks;

describe('PresetPackService', function () {
    it('lists available packs', function () {
        $service = app(PresetPackService::class);
        $packs = $service->listPacks();

        expect($packs)->toBeArray();
        expect($packs)->not->toBeEmpty();

        $names = array_column($packs, 'name');
        expect($names)->toContain('census-enumeration');
    });

    it('returns pack metadata from pack.md', function () {
        $service = app(PresetPackService::class);
        $packs = $service->listPacks();

        $censusPack = collect($packs)->firstWhere('name', 'census-enumeration');
        expect($censusPack)->not->toBeNull();
        expect($censusPack)->toHaveKey('title');
        expect($censusPack)->toHaveKey('description');
        expect($censusPack['title'])->toBe('Census Enumeration');
        expect($censusPack['description'])->toBe('Census enumeration operation monitoring — track daily enumeration progress, household completions, population counts, demographic breakdowns, time-to-complete projections, and case status distribution across enumeration areas.');
    });

    it('falls back to directory name as title when pack.md is missing', function () {
        $service = app(PresetPackService::class);

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getPackMetadata');
        $method->setAccessible(true);

        $result = $method->invoke($service, 'some-unknown-pack');

        expect($result['title'])->toBe('Some unknown pack');
        expect($result['description'])->toBe('');
    });

    it('returns artefact counts per type', function () {
        $service = app(PresetPackService::class);
        $packs = $service->listPacks();

        $censusPack = collect($packs)->firstWhere('name', 'census-enumeration');
        expect($censusPack)->not->toBeNull();
        expect($censusPack['artefacts']['scorecards'])->toBeInt();
        expect($censusPack['artefacts']['indicators'])->toBeInt();
        expect($censusPack['artefacts']['gauges'])->toBe(0);
        expect($censusPack['artefacts']['map-indicators'])->toBe(0);
        expect($censusPack['artefacts']['reports'])->toBeInt();
    });

    it('returns pack artefacts with parsed front matter', function () {
        $service = app(PresetPackService::class);
        $artefacts = $service->getPackArtefacts('census-enumeration');

        expect($artefacts)->toBeArray();
        expect($artefacts)->not->toBeEmpty();

        $scorecards = array_filter($artefacts, fn ($a) => $a['type'] === 'scorecard');
        expect($scorecards)->not->toBeEmpty();
        $scorecardNames = array_column($scorecards, 'name');

        $scorecard = collect($artefacts)->first(fn ($a) => $a['name'] === 'PopulationCount' && $a['type'] === 'scorecard');
        expect($scorecard)->not->toBeNull();
        expect($scorecard['title'])->toBe('Population Count');
        expect($scorecard['description'])->toBe('Total enumerated population across all enumeration areas');

        $indicator = collect($artefacts)->first(fn ($a) => $a['name'] === 'PopulationCount' && $a['type'] === 'indicator');
        expect($indicator)->not->toBeNull();
        expect($indicator['title'])->toBe('Population Count');
    });

    it('returns null content for missing artefact', function () {
        $service = app(PresetPackService::class);

        expect($service->getArtefactContent('census-enumeration', 'scorecard', 'NonExistent'))->toBeNull();
    });

    it('returns empty array for unknown pack', function () {
        $service = app(PresetPackService::class);

        expect($service->getPackArtefacts('nonexistent'))->toBe([]);
    });

    it('parses YAML front matter from markdown content', function () {
        $service = app(PresetPackService::class);
        $content = "---\ntype: scorecard\ntitle: Test\n---\nBody text";

        $parsed = $service->parseFrontMatter($content);
        expect($parsed)->toBe(['type' => 'scorecard', 'title' => 'Test']);
    });

    it('strips front matter leaving only body', function () {
        $service = app(PresetPackService::class);
        $content = "---\ntype: scorecard\n---\nBody text here";

        $body = $service->stripFrontMatter($content);
        expect($body)->toBe('Body text here');
    });

    it('returns empty array for content without front matter', function () {
        $service = app(PresetPackService::class);

        expect($service->parseFrontMatter('Just plain text'))->toBe([]);
    });
});

describe('GetPresetPacks tool', function () {
    it('lists packs with metadata and artefact counts', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(GetPresetPacks::class, []);

        $response->assertOk();
        $response->assertSee('census-enumeration');
        $response->assertSee('Census Enumeration');
        $response->assertSee('enumeration operation monitoring');
        $response->assertSee('scorecards');
        $response->assertSee('indicators');
    });
});
