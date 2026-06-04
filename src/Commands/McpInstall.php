<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;

class McpInstall extends Command
{
    protected $signature = 'chimera:mcp-install';
    protected $description = 'Configure the MCP server by registering dictionary files so AI agents can reference them by data source name';

    public function handle()
    {
        $configPath = base_path('chimera-mcp.json');

        $existing = [];
        if (File::exists($configPath)) {
            $existing = json_decode(File::get($configPath), true)['dictionaries'] ?? [];
        }

        $dictionaries = $existing;

        $this->line('chimera:mcp-install — MCP Server Setup');
        $this->line('────────────────────────────────────');

        if (! empty($dictionaries)) {
            info(count($dictionaries) . ' dictionary(s) already registered.');
            $this->table(
                ['Data Source', 'Path'],
                collect($dictionaries)->map(fn ($path, $name) => [$name, $path])->values()->toArray()
            );

            if (! confirm('Add more dictionaries?', default: false)) {
                $this->saveConfig($configPath, $dictionaries);
                info('Configuration unchanged.');

                return Command::SUCCESS;
            }
        }

        $dataSourcesExist = Schema::hasTable('data_sources');

        do {
            if ($dataSourcesExist) {
                $options = DB::table('data_sources')
                    ->orderBy('rank')
                    ->orderBy('name')
                    ->get()
                    ->mapWithKeys(fn ($ds) => [$ds->name => $ds->name . ' (' . $ds->title . ')'])
                    ->toArray();

                $name = search(
                    label: 'Select the data source this dictionary belongs to',
                    options: fn (string $value) => strlen($value) > 0
                        ? array_filter($options, fn ($label) => str_contains(strtolower($label), strtolower($value)), ARRAY_FILTER_USE_BOTH)
                        : $options,
                    required: true,
                    hint: 'Data sources are imported from your questionnaire database',
                );
            } else {
                $name = text(
                    label: 'Data source name',
                    placeholder: 'e.g., households',
                    required: true,
                    hint: 'Must match the data_source name used in create_* tools',
                );
            }

            $path = text(
                label: 'Dictionary file path',
                placeholder: 'e.g., /path/to/Dictionary.dcf',
                required: true,
                hint: 'Absolute path to the .dcf file (JSON or INI format)',
                validate: fn ($value) => File::exists($value) ? null : 'File not found at this path',
            );

            $dictionaries[$name] = $path;

            info("Registered: {$name} → {$path}");
        } while (confirm('Register another dictionary?', default: false));

        $this->saveConfig($configPath, $dictionaries);

        $this->line('');
        info('Configuration saved to ' . $configPath);
        $this->table(
            ['Data Source', 'Path'],
            collect($dictionaries)->map(fn ($path, $name) => [$name, $path])->values()->toArray()
        );

        info('AI agents can now reference these dictionaries by data source name in the parse-dictionary tool.');

        return Command::SUCCESS;
    }

    private function saveConfig(string $configPath, array $dictionaries): void
    {
        File::put($configPath, json_encode([
            'dictionaries' => $dictionaries,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
