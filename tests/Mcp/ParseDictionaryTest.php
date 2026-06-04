<?php

use Uneca\Chimera\Mcp\Servers\DashboardArtefactGenerator;
use Uneca\Chimera\Mcp\Tools\ParseDictionary;

$jsonDictionary = json_encode([
    'software' => 'CSPro',
    'version' => 8.1,
    'fileType' => 'dictionary',
    'name' => 'TEST_DICT',
    'labels' => [['text' => 'Test Dictionary']],
    'levels' => [
        [
            'name' => 'HOUSEHOLD',
            'labels' => [['text' => 'Household Level']],
            'ids' => [
                'items' => [
                    ['name' => 'HH_ID', 'labels' => [['text' => 'Household ID']], 'contentType' => 'Numeric', 'length' => 6],
                ],
            ],
            'records' => [
                [
                    'name' => 'POP_REC',
                    'labels' => [['text' => 'Population Record']],
                    'recordType' => 'Normal',
                    'occurrences' => [],
                    'items' => [
                        [
                            'name' => 'P00',
                            'labels' => [['text' => 'Name']],
                            'contentType' => 'Alpha',
                            'length' => 50,
                            'decimals' => null,
                            'valueSets' => [],
                        ],
                        [
                            'name' => 'P11',
                            'labels' => [['text' => 'Sex']],
                            'contentType' => 'Numeric',
                            'length' => 1,
                            'decimals' => null,
                            'valueSets' => [
                                [
                                    'name' => 'P11_VS1',
                                    'labels' => [['text' => 'Sex']],
                                    'values' => [
                                        ['labels' => [['text' => 'Male']], 'pairs' => [['value' => '1']]],
                                        ['labels' => [['text' => 'Female']], 'pairs' => [['value' => '2']]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
], JSON_PRETTY_PRINT);

$iniDictionary = <<<'INI'
[Dictionary]
Version=CSPro 7.6
Label=Test Dictionary
Name=TEST_DICT

[Level]
Label=Household Level
Name=HOUSEHOLD

[IdItems]

[Record]
Label=Population Record
Name=POP_REC
RecordTypeValue='70'
Required=No
MaxRecords=500
RecordLen=422

[Item]
Label=P00. Name
Name=P00
Start=73
Len=50
DataType=Alpha

[Item]
Label=P11. Sex
Name=P11
Start=125
Len=1

[ValueSet]
Label=P11. Sex
Name=P11_VS1
Value=1;Male
Note=#male
Value=2;Female
Note=#female
INI;

it('parses JSON dictionary', function () use ($jsonDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
    ]);

    $response->assertOk();
    $response->assertSee('TEST_DICT');
    $response->assertSee('HOUSEHOLD');
    $response->assertSee('POP_REC');
    $response->assertSee('P11');
    $response->assertSee('Male');
    $response->assertSee('Female');
});

it('rejects invalid JSON', function () {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => 'not json at all',
    ]);

    $response->assertHasErrors();
});

it('rejects non-dictionary JSON', function () use ($jsonDictionary) {
    $nonDict = json_decode($jsonDictionary, true);
    unset($nonDict['fileType']);
    $nonDict['fileType'] = 'application';

    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => json_encode($nonDict),
    ]);

    $response->assertHasErrors();
});

it('parses INI dictionary', function () use ($iniDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $iniDictionary,
    ]);

    $response->assertOk();
    $response->assertSee('TEST_DICT');
    $response->assertSee('HOUSEHOLD');
    $response->assertSee('POP_REC');
    $response->assertSee('P11');
    $response->assertSee('Male');
    $response->assertSee('Female');
});

it('rejects unrecognized format', function () {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => '<xml><dictionary></dictionary></xml>',
    ]);

    $response->assertHasErrors();
});

it('resolves registered dictionary by data source', function () use ($iniDictionary) {
    $configPath = base_path('chimera-mcp.json');
    $testDictPath = base_path('tests/Mcp/test_dict.dcf');
    @mkdir(dirname($testDictPath), 0777, true);
    file_put_contents($testDictPath, $iniDictionary);
    file_put_contents($configPath, json_encode([
        'dictionaries' => ['households' => $testDictPath],
    ]));

    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'data_source' => 'households',
    ]);

    $response->assertOk();
    $response->assertSee('TEST_DICT');
    $response->assertSee('P11');

    unlink($testDictPath);
    unlink($configPath);
    @rmdir(dirname($testDictPath));
});

it('returns error for unknown data source', function () {
    $configPath = base_path('chimera-mcp.json');
    @unlink($configPath);

    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'data_source' => 'NONEXISTENT',
    ]);

    $response->assertHasErrors();
});

it('filters by record_name', function () use ($jsonDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
        'record_name' => 'POP_REC',
    ]);

    $response->assertOk();
    $response->assertSee('"name": "POP_REC"');

    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
        'record_name' => 'NONEXISTENT',
    ]);

    $response->assertOk();
    $response->assertDontSee('"items"');
});

it('filters by item_name', function () use ($jsonDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
        'item_name' => 'P11',
    ]);

    $response->assertOk();
    $response->assertSee('"name": "P11"');
    $response->assertDontSee('"name": "P00"');
});

it('summary mode omits value sets', function () use ($jsonDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
        'summary' => true,
    ]);

    $response->assertOk();
    $response->assertDontSee('"valueSets"');
});

it('combines record_name and item_name filters', function () use ($jsonDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
        'record_name' => 'POP_REC',
        'item_name' => 'P11',
    ]);

    $response->assertOk();
    $response->assertSee('"name": "POP_REC"');
    $response->assertSee('"name": "P11"');
    $response->assertDontSee('"name": "P00"');
});

it('includes breakoutTable on each record', function () use ($jsonDictionary) {
    $response = DashboardArtefactGenerator::tool(ParseDictionary::class, [
        'content' => $jsonDictionary,
    ]);

    $response->assertOk();
    $response->assertSee('"breakoutTable"');
    $response->assertSee('"pop_rec"');
});
