<?php

use Laravel\Mcp\Server\Testing\PendingTestResponse;
use Uneca\Chimera\Mcp\Servers\DashboardStarterKit;
use Uneca\Chimera\Mcp\Tools\ReadDictionary;

$iniDictionary = <<<'INI'
[Dictionary]
Name=SampleDict
Label=Sample Dictionary
Version=7.0

[Level]
Name=HH
Label=Household Level

[IdItems]
AreaID=Area Identifier
ClusterNo=Cluster Number

[Record~HH_REC]
Name=HH_REC
Label=Household Record
RecordTypeValue=1
RecordLen=100
MaxRecords=1

[Item~HH_REC~HHID]
Name=HHID
Label=Household ID
DataType=String
Len=10
Start=1

[Item~HH_REC~H30]
Name=H30
Label=Rooms
DataType=Numeric
Len=2
Start=11

[ValueSet~H30]
Name=H30
Label=Room count
Value='1:5;1-5 rooms'
Note=Common range
Value='6;6 or more'
INI;

$jsonDictionary = <<<'JSON'
{
    "fileType": "dictionary",
    "name": "SampleDict",
    "labels": [{"text": "Sample Dictionary", "locale": "en"}],
    "levels": [
        {
            "name": "HH",
            "labels": [{"text": "Household Level", "locale": "en"}],
            "ids": {
                "items": [
                    {"name": "AreaID", "labels": [{"text": "Area Identifier", "locale": "en"}], "contentType": "String", "length": 10}
                ]
            },
            "records": [
                {
                    "name": "HH_REC",
                    "labels": [{"text": "Household Record", "locale": "en"}],
                    "items": [
                        {
                            "name": "HHID",
                            "labels": [{"text": "Household ID", "locale": "en"}],
                            "contentType": "String",
                            "length": 10,
                            "valueSets": []
                        }
                    ]
                }
            ]
        }
    ]
}
JSON;

describe('ReadDictionary MCP tool', function () use ($iniDictionary, $jsonDictionary) {
    it('parses INI dictionary format', function () use ($iniDictionary) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, ['content' => $iniDictionary]);

        $response->assertOk();
        $response->assertSee('"records"');
        $response->assertSee('"id_items"');
        $response->assertSee('HHID');
        $response->assertSee('H30');
        $response->assertSee('HH_REC');
        $response->assertSee('AreaID');
    });

    it('parses JSON dictionary format', function () use ($jsonDictionary) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, ['content' => $jsonDictionary]);

        $response->assertOk();
        $response->assertSee('"records"');
        $response->assertSee('HHID');
        $response->assertSee('HH_REC');
        $response->assertSee('AreaID');
    });

    it('parses value sets from INI format', function () use ($iniDictionary) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, ['content' => $iniDictionary]);

        $response->assertOk();
        $response->assertSee('"from": 1');
        $response->assertSee('"to": 5');
        $response->assertSee('"label": "1-5 rooms"');
        $response->assertSee('"value": 6');
        $response->assertSee('"label": "6 or more"');
        $response->assertSee('"note": "Common range"');
    });

    it('filters by record_name', function () use ($iniDictionary) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, [
                'content' => $iniDictionary,
                'record_name' => 'HH_REC',
            ]);

        $response->assertOk();
        $response->assertSee('HH_REC');
        $response->assertDontSee('NONEXISTENT');
    });

    it('filters by item_name', function () use ($iniDictionary) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, [
                'content' => $iniDictionary,
                'item_name' => 'HHID',
            ]);

        $response->assertOk();
        $response->assertSee('HHID');
        $response->assertDontSee('H30');
    });

    it('omits value sets in summary mode', function () use ($iniDictionary) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, [
                'content' => $iniDictionary,
                'summary' => true,
            ]);

        $response->assertOk();
        $response->assertDontSee('1-5 rooms');
        $response->assertDontSee('6 or more');
        $response->assertSee('HHID');
        $response->assertSee('H30');
    });

    it('returns error for unrecognized format', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, ['content' => 'not a dictionary']);

        $response->assertHasErrors(['Unrecognized format']);
    });

    it('returns error when data source has no registered dictionary', function () {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, ['data_source' => 'nonexistent']);

        $response->assertHasErrors(['No dictionary registered']);
    });
});
