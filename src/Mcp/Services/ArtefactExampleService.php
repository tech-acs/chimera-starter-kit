<?php

namespace Uneca\Chimera\Mcp\Services;

use Illuminate\Support\Facades\File;

class ArtefactExampleService
{
    public const TYPES = ['scorecard', 'gauge', 'indicator', 'map-indicator', 'report'];

    public function isValidType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }

    public function listExamples(string $type): array
    {
        $dir = $this->exampleDir($type);

        if (! is_dir($dir)) {
            return [];
        }

        $files = File::files($dir);
        $examples = [];

        foreach ($files as $file) {
            if (! $this->isValidReferenceFile($file)) {
                continue;
            }

            $examples[] = [
                'name' => $this->extractName($file),
                'description' => $this->extractDescription($file->getPathname()),
            ];
        }

        return $examples;
    }

    public function getExample(string $type, string $name): ?string
    {
        $path = $this->exampleDir($type) . '/' . $name . '.php.stub';

        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents !== false ? $contents : null;
    }

    public function getAvailableNames(string $type): array
    {
        $dir = $this->exampleDir($type);

        if (! is_dir($dir)) {
            return [];
        }

        $files = File::files($dir);
        $names = [];

        foreach ($files as $file) {
            if (! $this->isValidReferenceFile($file)) {
                continue;
            }

            $names[] = $this->extractName($file);
        }

        sort($names);

        return $names;
    }

    private function exampleDir(string $type): string
    {
        return __DIR__ . '/../../../resources/artefact-examples/' . $type;
    }

    private function isValidReferenceFile(\SplFileInfo $file): bool
    {
        $baseName = $file->getBasename('.stub');

        return $file->getExtension() === 'stub'
            && str_ends_with($baseName, '.php');
    }

    private function extractName(\SplFileInfo $file): string
    {
        return $file->getBasename('.php.stub');
    }

    private function extractDescription(string $path): string
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return '';
        }

        $description = '';
        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '// Description:')) {
                $description = trim(substr($trimmed, strlen('// Description:')));
                break;
            }
            if (! empty($trimmed) && ! str_starts_with($trimmed, '<?php')) {
                break;
            }
        }

        fclose($handle);

        return $description;
    }
}
