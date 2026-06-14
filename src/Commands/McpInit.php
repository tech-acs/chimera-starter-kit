<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Uneca\Chimera\Models\DataSource;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\table;

class McpInit extends Command
{
    protected $signature = 'chimera:mcp-init';
    protected $description = 'Configure the MCP server by registering dictionary files so AI agents can reference them by data source name';

    public function handle()
    {
        $configPath = base_path('dashboard-starter-kit-mcp.json');

        $dictionaries = [];
        if (File::exists($configPath)) {
            $dictionaries = json_decode(File::get($configPath), true)['dictionaries'] ?? [];
        }

        $dataSources = DataSource::active()->orderBy('name')->get();

        if ($dataSources->isEmpty()) {
            error('No active data sources found. Add data sources first, then re-run this command.');

            return Command::FAILURE;
        }

        $this->newLine()->info('Here are all the active data sources and their associated dictionaries');

        table(
            ['Data Source', 'Current Dictionary'],
            $dataSources->map(fn ($ds) => [
                "{$ds->name} ({$ds->title})",
                $dictionaries[$ds->name] ?? '<fg=gray>not set</>',
            ])->toArray()
        );

        foreach ($dataSources as $ds) {
            $existing = $dictionaries[$ds->name] ?? '';

            $path = text(
                label: "Dictionary file path for <options=bold>{$ds->name} ({$ds->title})</> data source",
                placeholder: 'e.g., /path/to/Dictionary.dcf',
                default: $existing,
                validate: fn ($value) => ($value === '' || File::exists($value))
                    ? null
                    : 'File not found at this path',
                hint: 'Relative path to the .dcf file. Leave empty to skip / clear.',
            );

            if ($path === '') {
                unset($dictionaries[$ds->name]);
                continue;
            }

            $dictionaries[$ds->name] = $path;
            info("Registered: {$ds->name} → {$path}");
        }

        $this->line('');
        $this->saveConfig($configPath, $dictionaries);

        info('Configuration saved to ' . $configPath);
        $this->table(
            ['Data Source', 'Path'],
            collect($dictionaries)->map(fn ($path, $name) => [$name, $path])->values()->toArray()
        );

        info('AI agents can now reference these dictionaries to understand the associated data sources.');

        return Command::SUCCESS;
    }

    private function saveConfig(string $configPath, array $dictionaries): void
    {
        File::put($configPath, json_encode([
                'dictionaries' => $dictionaries,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
