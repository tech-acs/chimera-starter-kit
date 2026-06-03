<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Parse a CSPro dictionary (.dcf) JSON file and return its structure: levels, records, items with types, lengths, labels, and value sets. Use this to understand what data fields are available when writing getData().')]
class ParseDictionary extends Tool
{
    public function handle(Request $request): Response
    {
        $jsonContent = $request->string('content');

        $dictionary = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return Response::error('Invalid JSON: '.json_last_error_msg());
        }

        if (($dictionary['fileType'] ?? '') !== 'dictionary') {
            return Response::error('Not a valid CSPro dictionary file. Expected fileType "dictionary".');
        }

        $result = [
            'name' => $dictionary['name'] ?? 'Unnamed',
            'labels' => $dictionary['labels'] ?? [],
            'levels' => [],
        ];

        foreach ($dictionary['levels'] ?? [] as $level) {
            $parsedLevel = [
                'name' => $level['name'] ?? '',
                'labels' => $level['labels'] ?? [],
                'idItems' => [],
                'records' => [],
            ];

            foreach ($level['ids']['items'] ?? [] as $item) {
                $parsedLevel['idItems'][] = [
                    'name' => $item['name'],
                    'label' => $item['labels'][0]['text'] ?? '',
                    'type' => $item['contentType'],
                    'length' => $item['length'],
                ];
            }

            foreach ($level['records'] ?? [] as $record) {
                $parsedRecord = [
                    'name' => $record['name'],
                    'label' => $record['labels'][0]['text'] ?? '',
                    'recordType' => $record['recordType'] ?? '',
                    'occurrences' => $record['occurrences'] ?? [],
                    'items' => [],
                ];

                foreach ($record['items'] ?? [] as $item) {
                    $parsedItem = [
                        'name' => $item['name'],
                        'label' => $item['labels'][0]['text'] ?? '',
                        'type' => $item['contentType'],
                        'length' => $item['length'],
                        'decimals' => $item['decimals'] ?? null,
                        'valueSets' => [],
                    ];

                    foreach ($item['valueSets'] ?? [] as $valueSet) {
                        $values = [];
                        foreach ($valueSet['values'] ?? [] as $value) {
                            foreach ($value['pairs'] ?? [] as $pair) {
                                if (isset($pair['range'])) {
                                    $values[] = [
                                        'label' => $value['labels'][0]['text'] ?? '',
                                        'range' => $pair['range'],
                                    ];
                                } elseif (isset($pair['value'])) {
                                    $values[] = [
                                        'label' => $value['labels'][0]['text'] ?? '',
                                        'value' => $pair['value'],
                                    ];
                                }
                            }
                        }
                        $parsedItem['valueSets'][] = [
                            'name' => $valueSet['name'] ?? '',
                            'label' => $valueSet['labels'][0]['text'] ?? '',
                            'values' => $values,
                        ];
                    }

                    $parsedRecord['items'][] = $parsedItem;
                }

                $parsedLevel['records'][] = $parsedRecord;
            }

            $result['levels'][] = $parsedLevel;
        }

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string(
                description: 'The raw JSON content of the .dcf dictionary file',
            ),
        ];
    }
}
