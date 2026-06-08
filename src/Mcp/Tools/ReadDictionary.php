<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\File;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Parse a CSPro dictionary (.dcf) file and return its structure: levels, records, items with types, lengths, labels, and value sets. Use this to understand what data fields are available when writing getData(). Accepts both JSON (CSPro 8+) and INI (pre-CSPro 8.0) formats. You can provide the raw content, or pass a data_source name that was registered via chimera:mcp-init.')]
class ReadDictionary extends Tool
{
    public function handle(Request $request): Response
    {
        $dataSource = (string) $request->string('data_source', '');

        if (! empty($dataSource)) {
            $content = $this->loadFromConfig($dataSource);
            if ($content === null) {
                $registered = implode(', ', array_keys($this->registeredDictionaries()));
                return Response::error("No dictionary registered for data source '{$dataSource}'. Registered data sources: {$registered}");
            }
        } else {
            $content = (string) $request->string('content', '');
        }

        $trimmed = trim($content);

        if (str_starts_with($trimmed, '{')) {
            $response = $this->parseJson($content);
        } elseif (str_starts_with($trimmed, '[')) {
            $response = $this->parseIni($content);
        } else {
            return Response::error('Unrecognized format. Dictionary content must be JSON (starts with {) or INI (starts with [).');
        }

        if ($response->isError()) {
            return $response;
        }

        $data = json_decode((string) $response->content(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $response;
        }

        $recordName = (string) $request->string('record_name', '');
        $itemName = (string) $request->string('item_name', '');
        $summary = $request->boolean('summary', false);

        foreach ($data['levels'] as &$level) {
            $filteredRecords = [];
            foreach ($level['records'] as &$record) {
                $record['breakoutTable'] = strtolower($record['name']);

                if ($recordName !== '' && $record['name'] !== $recordName) {
                    continue;
                }

                if ($itemName !== '') {
                    $record['items'] = array_values(array_filter($record['items'], fn (array $item) => $item['name'] === $itemName));
                }

                if ($summary) {
                    $record['items'] = array_map(fn (array $item) => array_diff_key($item, ['valueSets' => true]), $record['items']);
                }

                $filteredRecords[] = $record;
            }
            $level['records'] = $filteredRecords;
        }
        unset($level, $record);

        return Response::text(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function parseJson(string $jsonContent): Response
    {
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

    private function parseIni(string $iniContent): Response
    {
        $sections = $this->parseIniSections($iniContent);

        $dictSection = $sections['Dictionary'][0] ?? [];

        $levelSections = $sections['Level'] ?? [];
        $idItemSections = $sections['IdItems'] ?? [];
        $itemSections = $sections['Item'] ?? [];
        $recordSections = $sections['Record'] ?? [];
        $valueSetSections = $sections['ValueSet'] ?? [];

        $parsedValueSets = [];
        foreach ($valueSetSections as $vs) {
            $values = [];
            foreach ($vs['_values'] ?? [] as $valueEntry) {
                $parsedValues = $this->parseIniValue($valueEntry);
                $values = array_merge($values, $parsedValues);
            }
            $parsedValueSets[$vs['Name'] ?? ''] = [
                'name' => $vs['Name'] ?? '',
                'label' => $vs['Label'] ?? '',
                'values' => $values,
            ];
        }

        $parsedItems = [];
        foreach ($itemSections as $item) {
            $itemValueSets = [];
            $itemName = $item['Name'] ?? '';
            foreach ($parsedValueSets as $vsName => $vs) {
                if (str_starts_with($vsName, $itemName . '_VS') || str_starts_with($vsName, $itemName . '_') || $vsName === $itemName) {
                    if ($this->valueSetBelongsToItem($itemName, $vsName, $parsedValueSets)) {
                        $itemValueSets[] = $vs;
                    }
                }
            }
            $parsedItems[] = [
                'name' => $itemName,
                'label' => $item['Label'] ?? '',
                'type' => isset($item['DataType']) ? $item['DataType'] : 'Numeric',
                'length' => (int) ($item['Len'] ?? 0),
                'start' => (int) ($item['Start'] ?? 0),
                'valueSets' => $itemValueSets,
            ];
        }

        $parsedRecords = [];
        foreach ($recordSections as $record) {
            $recordItems = [];
            $recordName = $record['Name'] ?? '';
            foreach ($parsedItems as $item) {
                if ($this->itemBelongsToRecord($item['name'], $recordName, $parsedRecords, $parsedItems)) {
                    $recordItems[] = $item;
                }
            }
            if (empty($recordItems)) {
                $recordItems = $parsedItems;
            }
            $parsedRecords[] = [
                'name' => $recordName,
                'label' => $record['Label'] ?? '',
                'recordType' => $record['RecordTypeValue'] ?? '',
                'maxRecords' => isset($record['MaxRecords']) ? (int) $record['MaxRecords'] : null,
                'recordLen' => isset($record['RecordLen']) ? (int) $record['RecordLen'] : null,
                'items' => $recordItems,
            ];
        }

        $result = [
            'name' => $dictSection['Name'] ?? 'Unnamed',
            'label' => $dictSection['Label'] ?? '',
            'version' => $dictSection['Version'] ?? '',
            'format' => 'INI',
        ];

        foreach ($levelSections as $level) {
            $levelRecords = $parsedRecords;

            $levelName = $level['Name'] ?? '';
            $idItems = [];
            if (! empty($idItemSections)) {
                foreach ($idItemSections as $idSection) {
                    foreach ($idSection as $key => $value) {
                        if (! str_starts_with($key, '_')) {
                            $idItems[] = [
                                'name' => $key,
                                'label' => $value,
                            ];
                        }
                    }
                }
            }

            $result['levels'][] = [
                'name' => $levelName,
                'label' => $level['Label'] ?? '',
                'idItems' => $idItems,
                'records' => $levelRecords,
            ];
        }

        if (empty($result['levels'])) {
            $result['levels'][] = [
                'name' => '',
                'label' => '',
                'idItems' => [],
                'records' => $parsedRecords,
            ];
        }

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    private function parseIniSections(string $content): array
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $sections = [];
        $currentSection = null;
        $currentSectionName = null;
        $inValue = false;
        $currentValueLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($inValue) {
                    $currentValueLines[] = '';
                }
                continue;
            }

            if (preg_match('/^\[(.+)\]$/', $trimmed, $matches)) {
                if ($currentSectionName !== null && $currentSection !== null) {
                    if ($inValue && ! empty($currentValueLines)) {
                        $currentSection['_values'][] = implode("\n", $currentValueLines);
                        $currentValueLines = [];
                    }
                    $inValue = false;
                    $sections[$currentSectionName][] = $currentSection;
                }

                $currentSectionName = $matches[1];
                $currentSection = ['_values' => []];
                $inValue = false;
                $currentValueLines = [];
                continue;
            }

            if ($inValue) {
                if (preg_match('/^(Value|Name|Note)=\s*(.*)$/', $trimmed, $m)) {
                    $currentValueLines[] = $trimmed;
                } else {
                    $currentSection['_values'][] = implode("\n", $currentValueLines);
                    $currentValueLines = [];
                    $inValue = false;
                }
                if ($inValue) {
                    continue;
                }
            }

            if (preg_match('/^Value=\s*(.*)$/', $trimmed, $matches)) {
                $inValue = true;
                $currentValueLines = [$trimmed];
                continue;
            }

            if (preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)=(.*)$/', $trimmed, $matches)) {
                $key = $matches[1];
                $value = trim($matches[2]);
                $currentSection[$key] = $value;
            }
        }

        if ($currentSectionName !== null && $currentSection !== null) {
            if ($inValue && ! empty($currentValueLines)) {
                $currentSection['_values'][] = implode("\n", $currentValueLines);
            }
            $sections[$currentSectionName][] = $currentSection;
        }

        return $sections;
    }

    private function parseIniValue(string $valueText): array
    {
        $values = [];
        $lines = explode("\n", $valueText);
        $valueLine = '';
        $note = null;
        $name = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^Value=\s*(.*)$/', $trimmed, $m)) {
                if (! empty($valueLine)) {
                    $parsed = $this->parseSingleIniValue($valueLine, $note, $name);
                    if ($parsed !== null) {
                        $values[] = $parsed;
                    }
                    $note = null;
                    $name = null;
                }
                $valueLine = $m[1];
            } elseif (preg_match('/^Note=(.*)$/', $trimmed, $m)) {
                $note = trim($m[1]);
            } elseif (preg_match('/^Name=(.*)$/', $trimmed, $m)) {
                $name = trim($m[1]);
            }
        }

        if (! empty($valueLine)) {
            $parsed = $this->parseSingleIniValue($valueLine, $note, $name);
            if ($parsed !== null) {
                $values[] = $parsed;
            }
        }

        return $values;
    }

    private function parseSingleIniValue(string $valuePart, ?string $note, ?string $name): ?array
    {
        $valuePart = trim($valuePart);

        if (preg_match('/^\'(.+?)\'$/', $valuePart, $m)) {
            $valuePart = $m[1];
        }

        if (preg_match('/^(\d+):(\d+);(.+)$/', $valuePart, $m)) {
            $result = [
                'from' => (int) $m[1],
                'to' => (int) $m[2],
                'label' => trim($m[3]),
            ];
            if ($note !== null) {
                $result['note'] = $note;
            }
            if ($name !== null) {
                $result['name'] = $name;
            }
            return $result;
        }

        if (preg_match('/^(\d+);(.+)$/', $valuePart, $m)) {
            $result = [
                'value' => (int) $m[1],
                'label' => trim($m[2]),
            ];
            if ($note !== null) {
                $result['note'] = $note;
            }
            if ($name !== null) {
                $result['name'] = $name;
            }
            return $result;
        }

        return [
            'raw' => $valuePart,
        ];
    }

    private function valueSetBelongsToItem(string $itemName, string $vsName, array $allValueSets): bool
    {
        if ($vsName === $itemName) {
            return true;
        }
        $prefix = $itemName . '_VS';
        if (str_starts_with($vsName, $prefix)) {
            $suffix = substr($vsName, strlen($prefix));
            return is_numeric($suffix);
        }
        $prefix2 = $itemName . '_';
        if (str_starts_with($vsName, $prefix2) && $vsName !== $itemName) {
            return true;
        }
        return false;
    }

    private function itemBelongsToRecord(string $itemName, string $recordName, array $parsedRecords, array $allItems): bool
    {
        return true;
    }

    private function loadFromConfig(string $name): ?string
    {
        $dictionaries = $this->registeredDictionaries();

        if (! isset($dictionaries[$name])) {
            return null;
        }

        $path = $dictionaries[$name];

        if (! File::exists($path)) {
            return null;
        }

        return File::get($path);
    }

    private function registeredDictionaries(): array
    {
        $configPath = base_path('chimera-mcp.json');

        if (! File::exists($configPath)) {
            return [];
        }

        $config = json_decode(File::get($configPath), true);

        return $config['dictionaries'] ?? [];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->description('The raw content of the .dcf dictionary file (JSON or INI format). Use your file-reading tool to get this content, then pass it here. Alternatively, use dictionary_name for a pre-registered dictionary.'),
            'data_source' => $schema->string()->description('The data source name (from list-data-sources). The associated dictionary file was registered via chimera:mcp-install. If provided, reads the file automatically.'),
            'record_name' => $schema->string()->description('Filter output to only include records with this name (e.g. "POP_REC"). Use summary=true first to discover record names.'),
            'item_name' => $schema->string()->description('Filter output to only include items with this name across all records (e.g. "H30"). Combine with record_name for precise drill-down.'),
            'summary' => $schema->boolean()->description('If true, omit value sets from the output — only return names, types, and lengths. Use this first to browse the structure without the overhead of full value sets.'),
        ];
    }
}
