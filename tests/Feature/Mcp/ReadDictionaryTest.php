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

$jsonDictionaryWithValuesets = <<<'JSON'
{
    "software": "CSPro",
    "version": 8.0,
    "fileType": "dictionary",
    "name": "TestDict",
    "levels": [
        {
            "name": "HH",
            "labels": [{"text": "Household"}],
            "ids": { "items": [] },
            "records": [
                {
                    "name": "PERSON",
                    "labels": [{"text": "Person Record"}],
                    "recordType": "1",
                    "items": [
                        {
                            "name": "P02_REL",
                            "labels": [{"text": "Relationship"}],
                            "contentType": "numeric",
                            "length": 1,
                            "valueSets": [
                                {
                                    "name": "P02_REL_VS1",
                                    "labels": [{"text": "Relationship"}],
                                    "values": [
                                        { "labels": [{"text": "Head"}], "pairs": [{"value": "1"}] },
                                        { "labels": [{"text": "Spouse"}], "pairs": [{"value": "2"}] },
                                        { "labels": [{"text": "Child"}], "pairs": [{"value": "3"}] }
                                    ]
                                }
                            ]
                        },
                        {
                            "name": "P04_AGE",
                            "labels": [{"text": "Age"}],
                            "contentType": "numeric",
                            "length": 2,
                            "valueSets": [
                                {
                                    "name": "P04_AGE_VS1",
                                    "labels": [{"text": "Age groups"}],
                                    "values": [
                                        { "labels": [{"text": "0 to 4 years"}], "pairs": [{"range": ["0", "4"]}] },
                                        { "labels": [{"text": "5 to 9 years"}], "pairs": [{"range": ["5", "9"]}] }
                                    ]
                                }
                            ]
                        },
                        {
                            "name": "P03_SEX",
                            "labels": [{"text": "Sex"}],
                            "contentType": "numeric",
                            "length": 1,
                            "valueSets": [
                                {
                                    "name": "P03_SEX_VS1",
                                    "labels": [{"text": "Sex"}],
                                    "values": [
                                        { "labels": [{"text": "Male"}], "pairs": [{"value": "1"}] },
                                        { "labels": [{"text": "Female"}], "pairs": [{"value": "2"}] }
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ]
}
JSON;

describe('ReadDictionary MCP tool', function () use ($iniDictionary, $jsonDictionary, $jsonDictionaryWithValuesets) {
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

    it('parses value sets from JSON format', function () use ($jsonDictionaryWithValuesets) {
        $response = (new PendingTestResponse($this->app, DashboardStarterKit::class))
            ->tool(ReadDictionary::class, ['content' => $jsonDictionaryWithValuesets]);

        $response->assertOk();
        $response->assertSee('"records"');
        $response->assertSee('P02_REL');
        $response->assertSee('P04_AGE');
        $response->assertSee('P03_SEX');
        $response->assertSee('"label": "Head"');
        $response->assertSee('"value": 1');
        $response->assertSee('"label": "Spouse"');
        $response->assertSee('"value": 2');
        $response->assertSee('"label": "Male"');
        $response->assertSee('"label": "Female"');
        $response->assertSee('"from": "0"');
        $response->assertSee('"to": "4"');
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
