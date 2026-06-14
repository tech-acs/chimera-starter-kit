<?php

namespace Uneca\Chimera\Services;

class DictionaryParser
{
    private array $keyMapping = [
        'Name' => 'name',
        'Label' => 'label',
        'RecordTypeValue' => 'recordType',
        'DataType' => 'type',
    ];

    private array $excludeItemKeys = ['Len', 'Start', 'Decimal', 'ZeroFill', 'DecimalChar', 'Occurrences', 'ItemType'];

    private array $excludeRecordKeys = ['RecordLen', 'MaxRecords', 'Required', 'Positions', 'RecordTypeStart', 'RecordTypeLen'];

    public function parseToJson(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);

        return $this->parseContentToJson($content);
    }

    public function parseContentToJson(string $content): string
    {
        // Strip BOM
        $content = str_replace(["\ufeff", "\u00ef", "\u00bb", "\u00bf"], '', $content);

        $trimmed = trim($content);

        if (str_starts_with($trimmed, '{')) {
            $dictionary = $this->parseJsonDictionary($content);
        } else {
            if (! preg_match('/^\[.*\]$/m', $trimmed)) {
                throw new \Exception('Unrecognized format. Dictionary content must be JSON (starts with {) or INI with sections like [Record], [Item], [ValueSet].');
            }

            $dictionary = $this->parseIniDictionary($content);
        }

        $normalized = $this->normalize($dictionary);

        return json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function parseJsonDictionary(string $content): array
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON: ' . json_last_error_msg());
        }

        $idItems = [];
        $records = [];

        foreach ($data['levels'] ?? [] as $level) {
            foreach ($level['ids']['items'] ?? [] as $item) {
                $idItems[] = $this->parseJsonItem($item);
            }

            foreach ($level['records'] ?? [] as $record) {
                $parsedRecord = [
                    'Name' => $record['name'],
                    'Label' => $record['labels'][0]['text'] ?? '',
                    'RecordTypeValue' => $record['recordType'] ?? '',
                ];

                foreach ($record['items'] ?? [] as $item) {
                    $parsedRecord['items'][] = $this->parseJsonItem($item);
                }

                $records[] = $parsedRecord;
            }
        }

        return ['id_items' => $idItems, 'records' => $records];
    }

    private function parseJsonItem(array $item): array
    {
        $parsed = [
            'Name' => $item['name'],
            'Label' => $item['labels'][0]['text'] ?? '',
            'DataType' => $item['contentType'] ?? 'Numeric',
        ];

        $valueSets = [];
        foreach ($item['valueSets'] ?? [] as $valueSet) {
            $values = [];
            foreach ($valueSet['values'] ?? [] as $value) {
                foreach ($value['pairs'] ?? [] as $pair) {
                    $entry = ['Label' => $value['labels'][0]['text'] ?? ''];
                    if (isset($pair['range'])) {
                        $entry['range'] = $pair['range'];
                    } elseif (isset($pair['value'])) {
                        $entry['value'] = $pair['value'];
                    }
                    $values[] = $entry;
                }
            }
            $valueSets[] = [
                'Name' => $valueSet['name'] ?? '',
                'Label' => $valueSet['labels'][0]['text'] ?? '',
                'values' => $values,
            ];
        }

        if (! empty($valueSets)) {
            $parsed['valueSets'] = $valueSets;
        }

        return $parsed;
    }

    private function parseIniDictionary(string $content): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $content);
        $lines = array_filter($lines, fn ($l) => trim($l) !== '');
        $lines = array_values($lines);

        $dictionary = [
            'id_items' => [],
            'records' => [],
        ];

        $currentRecord = null;
        $currentItem = null;
        $currentValueSet = null;
        $context = null;

        $pushValueSet = function () use (&$currentItem, &$currentValueSet) {
            if ($currentValueSet && $currentItem !== null) {
                $currentItem['valueSets'][] = $currentValueSet;
            }
            $currentValueSet = null;
        };

        $pushItem = function () use (&$dictionary, &$currentRecord, &$currentItem, $pushValueSet) {
            if ($currentItem) {
                $pushValueSet();
                if ($currentRecord !== null) {
                    $currentRecord['items'][] = $currentItem;
                } else {
                    $dictionary['id_items'][] = $currentItem;
                }
            }
            $currentItem = null;
        };

        $pushRecord = function () use (&$dictionary, &$currentRecord, $pushItem) {
            if ($currentRecord !== null) {
                $pushItem();
                $dictionary['records'][] = $currentRecord;
            }
            $currentRecord = null;
        };

        $assignValue = function (&$target, $key, $value) {
            if ($key === 'Value') {
                if (! isset($target['values'])) {
                    $target['values'] = [];
                }
                $target['values'][] = $value;
            } else {
                if (isset($target[$key])) {
                    if (is_array($target[$key])) {
                        $target[$key][] = $value;
                    } else {
                        $target[$key] = [$target[$key], $value];
                    }
                } else {
                    $target[$key] = $value;
                }
            }
        };

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^\[(.*?)\]$/', $line, $matches)) {
                $section = $matches[1];

                if (str_starts_with($section, 'Record')) {
                    if ($currentRecord !== null) {
                        $pushRecord();
                    } elseif ($currentItem !== null) {
                        $pushItem();
                    }

                    $currentRecord = ['items' => []];
                    $context = 'record';
                } elseif (str_starts_with($section, 'Item')) {
                    $pushItem();
                    $currentItem = ['valueSets' => []];
                    $context = 'item';
                } elseif (str_starts_with($section, 'ValueSet')) {
                    $pushValueSet();
                    $currentValueSet = ['values' => []];
                    $context = 'valueset';
                } elseif (str_starts_with($section, 'IdItems')) {
                    if ($currentItem !== null) {
                        $pushItem();
                    }
                    $context = 'iditems';
                } else {
                    $context = strtolower($section);
                }
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                if ($context === 'record' && $currentRecord !== null) {
                    $assignValue($currentRecord, $key, $value);
                } elseif ($context === 'item' && $currentItem !== null) {
                    $assignValue($currentItem, $key, $value);
                } elseif ($context === 'valueset' && $currentValueSet !== null) {
                    $assignValue($currentValueSet, $key, $value);
                } elseif ($context === 'iditems') {
                    $dictionary['id_items'][] = [
                        'Name' => $key,
                        'Label' => $value,
                    ];
                }
            }
        }

        if ($currentRecord !== null) {
            $pushRecord();
        } elseif ($currentItem !== null) {
            $pushItem();
        }

        return $dictionary;
    }

    private function normalize(array $dictionary): array
    {
        return [
            'id_items' => array_map(fn (array $item) => $this->normalizeItem($item), $dictionary['id_items']),
            'records' => array_map(fn (array $record) => $this->normalizeRecord($record), $dictionary['records']),
        ];
    }

    private function normalizeRecord(array $record): array
    {
        $normalized = [];

        foreach ($record as $key => $value) {
            if (in_array($key, $this->excludeRecordKeys, true)) {
                continue;
            }
            $normalizedKey = $this->keyMapping[$key] ?? lcfirst($key);
            $normalized[$normalizedKey] = $this->castValue($value);
        }

        if (isset($record['items'])) {
            $normalized['items'] = array_map(fn (array $item) => $this->normalizeItem($item), $record['items']);
        }

        $normalized['breakoutTable'] = strtolower($normalized['name'] ?? '');

        return $normalized;
    }

    private function normalizeItem(array $item): array
    {
        $normalized = [];

        foreach ($item as $key => $value) {
            if (in_array($key, $this->excludeItemKeys, true)) {
                continue;
            }
            $normalizedKey = $this->keyMapping[$key] ?? lcfirst($key);
            $normalized[$normalizedKey] = $this->castValue($value);
        }

        if (isset($item['valueSets'])) {
            $normalized['valueSets'] = array_map(fn (array $vs) => $this->normalizeValueSet($vs), $item['valueSets']);
        }

        return $normalized;
    }

    private function normalizeValueSet(array $valueSet): array
    {
        $normalized = [];

        foreach ($valueSet as $key => $value) {
            $normalizedKey = $this->keyMapping[$key] ?? lcfirst($key);
            $normalized[$normalizedKey] = $this->castValue($value);
        }

        if (isset($normalized['values'])) {
            $normalized['values'] = array_map(
                fn (string $raw) => $this->parseIniValueEntry(trim($raw, "'")),
                $normalized['values']
            );
        }

        return $normalized;
    }

    private function parseIniValueEntry(string $valuePart): array
    {
        if (preg_match('/^(\d+):(\d+);(.+)$/', $valuePart, $m)) {
            return [
                'from' => (int) $m[1],
                'to' => (int) $m[2],
                'label' => trim($m[3]),
            ];
        }

        if (preg_match('/^(\d+);(.+)$/', $valuePart, $m)) {
            return [
                'value' => (int) $m[1],
                'label' => trim($m[2]),
            ];
        }

        return ['raw' => $valuePart];
    }

    private function castValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value, "'");
        }

        return $value;
    }
}
