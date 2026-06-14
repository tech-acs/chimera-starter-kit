<?php

namespace Uneca\Chimera\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\File;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Uneca\Chimera\Services\DictionaryParser;

#[Description('Read a CSPro dictionary (.dcf) file and return its structure: records (tables) with their items (columns), labels, types, and value sets. Use this to understand what data fields are available when writing getData(). Accepts both JSON (CSPro 8+) and INI (pre-CSPro 8.0) formats. You can provide the raw content, or pass a data_source name that was registered via chimera:mcp-init.')]
class ReadDictionary extends Tool
{
    public function handle(Request $request): Response
    {
        $dataSource = (string) $request->string('data_source', '');

        if (! empty($dataSource)) {
            $path = $this->getDictionaryPath($dataSource);

            if ($path === null) {
                $registered = implode(', ', array_keys($this->registeredDictionaries()));

                return Response::error("No dictionary registered for data source '{$dataSource}'. Registered data sources: {$registered}");
            }

            try {
                $json = (new DictionaryParser)->parseToJson($path);
            } catch (\Exception $e) {
                return Response::error($e->getMessage());
            }
        } else {
            $content = (string) $request->string('content', '');

            if (empty($content)) {
                return Response::error('Either content or data_source must be provided');
            }

            try {
                $json = (new DictionaryParser)->parseContentToJson($content);
            } catch (\Exception $e) {
                return Response::error($e->getMessage());
            }
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return Response::error('Failed to decode parsed dictionary');
        }

        $recordName = (string) $request->string('record_name', '');
        $itemName = (string) $request->string('item_name', '');
        $summary = $request->boolean('summary', false);

        $filteredRecords = [];
        foreach ($data['records'] as &$record) {
            if ($recordName !== '' && $record['name'] !== $recordName) {
                continue;
            }

            if ($itemName !== '') {
                $record['items'] = array_values(array_filter(
                    $record['items'],
                    fn (array $item) => $item['name'] === $itemName
                ));
            }

            if ($summary) {
                $data['id_items'] = array_map(
                    fn (array $item) => array_diff_key($item, ['valueSets' => true]),
                    $data['id_items'] ?? []
                );
                $record['items'] = array_map(
                    fn (array $item) => array_diff_key($item, ['valueSets' => true]),
                    $record['items']
                );
            }

            $filteredRecords[] = $record;
        }
        $data['records'] = $filteredRecords;

        return Response::text(json_encode($data, JSON_PRETTY_PRINT));
    }

    private function getDictionaryPath(string $name): ?string
    {
        $dictionaries = $this->registeredDictionaries();

        if (! isset($dictionaries[$name])) {
            return null;
        }

        $path = $dictionaries[$name];

        if (! File::exists($path)) {
            return null;
        }

        return $path;
    }

    private function registeredDictionaries(): array
    {
        $configPath = base_path('dashboard-starter-kit-mcp.json');

        if (! File::exists($configPath)) {
            return [];
        }

        $config = json_decode(File::get($configPath), true);

        return $config['dictionaries'] ?? [];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'content' => $schema->string()->description('The raw content of the .dcf dictionary file (JSON or INI format). Use your file-reading tool to get this content, then pass it here. Alternatively, use data_source for a pre-registered dictionary.'),
            'data_source' => $schema->string()->description('The data source name (from get-data-sources). The associated dictionary file was registered via chimera:mcp-init. If provided, reads the file automatically.'),
            'record_name' => $schema->string()->description('Filter output to only include records with this name (e.g. "POP_REC"). Use summary=true first to discover record names.'),
            'item_name' => $schema->string()->description('Filter output to only include items with this name across all records (e.g. "H30"). Combine with record_name for precise drill-down.'),
            'summary' => $schema->boolean()->description('If true, omit value sets from the output — only return names, types, and lengths. Use this first to browse the structure without the overhead of full value sets.'),
        ];
    }
}
