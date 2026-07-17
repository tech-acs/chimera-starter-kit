<?php

namespace Uneca\Chimera\Mcp\Services;

use Illuminate\Support\Facades\File;

class PresetPackService
{
    public const TYPES = ['indicators', 'scorecards', 'gauges', 'map-indicators', 'reports'];

    public function listPacks(): array
    {
        $packsDir = $this->packsDir();

        if (! is_dir($packsDir)) {
            return [];
        }

        $directories = File::directories($packsDir);
        $packs = [];

        foreach ($directories as $dir) {
            $packName = basename($dir);
            $metadata = $this->getPackMetadata($packName);
            $packs[] = [
                'name' => $packName,
                'title' => $metadata['title'],
                'description' => $metadata['description'],
                'artefacts' => $this->artefactCountByType($packName),
            ];
        }

        sort($packs);

        return $packs;
    }

    public function getPackArtefacts(string $pack): array
    {
        $packDir = $this->packDir($pack);

        if (! is_dir($packDir)) {
            return [];
        }

        $artefacts = [];

        foreach (self::TYPES as $type) {
            $typeDir = $packDir.'/'.$type;

            if (! is_dir($typeDir)) {
                continue;
            }

            $files = File::files($typeDir);

            foreach ($files as $file) {
                if (! $this->isValidArtefactFile($file)) {
                    continue;
                }

                $content = file_get_contents($file->getPathname());

                if ($content === false) {
                    continue;
                }

                $frontMatter = $this->parseFrontMatter($content);
                $name = $this->extractName($file);

                $artefacts[] = [
                    'name' => $name,
                    'file' => $file->getFilename(),
                    'type' => $this->singularType($type),
                    'title' => $frontMatter['title'] ?? $name,
                    'description' => $frontMatter['description'] ?? '',
                    'body' => $this->stripFrontMatter($content),
                ];
            }
        }

        return $artefacts;
    }

    public function getArtefactContent(string $pack, string $type, string $name): ?string
    {
        $path = $this->packDir($pack).'/'.$this->pluralType($type).'/'.$name.'.md';

        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        return $contents !== false ? $contents : null;
    }

    public function getArtefactFrontMatter(string $pack, string $type, string $name): ?array
    {
        $content = $this->getArtefactContent($pack, $type, $name);

        if ($content === null) {
            return null;
        }

        return $this->parseFrontMatter($content);
    }

    public function parseFrontMatter(string $content): array
    {
        if (! str_starts_with(trim($content), '---')) {
            return [];
        }

        $parts = explode('---', trim($content), 3);

        if (count($parts) < 3) {
            return [];
        }

        $parsed = [];
        $lines = explode("\n", trim($parts[1]));

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $colonPos = strpos($line, ':');

            if ($colonPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $colonPos));
            $value = trim(substr($line, $colonPos + 1));
            $parsed[$key] = $value;
        }

        return $parsed;
    }

    public function stripFrontMatter(string $content): string
    {
        if (! str_starts_with(trim($content), '---')) {
            return $content;
        }

        $parts = explode('---', trim($content), 3);

        if (count($parts) < 3) {
            return $content;
        }

        return trim($parts[2]);
    }

    public function artefactCountByType(string $pack): array
    {
        $packDir = $this->packDir($pack);

        if (! is_dir($packDir)) {
            return [];
        }

        $counts = [];

        foreach (self::TYPES as $type) {
            $counts[$type] = 0;
            $typeDir = $packDir.'/'.$type;

            if (! is_dir($typeDir)) {
                continue;
            }

            foreach (File::files($typeDir) as $file) {
                if ($this->isValidArtefactFile($file)) {
                    $counts[$type]++;
                }
            }
        }

        return $counts;
    }

    private function getPackMetadata(string $pack): array
    {
        $packDir = $this->packDir($pack);
        $manifestPath = $packDir.'/pack.md';

        if (! file_exists($manifestPath)) {
            return [
                'title' => ucfirst(str_replace('-', ' ', $pack)),
                'description' => '',
            ];
        }

        $content = file_get_contents($manifestPath);

        if ($content === false) {
            return [
                'title' => ucfirst(str_replace('-', ' ', $pack)),
                'description' => '',
            ];
        }

        $frontMatter = $this->parseFrontMatter($content);

        return [
            'title' => $frontMatter['title'] ?? ucfirst(str_replace('-', ' ', $pack)),
            'description' => $frontMatter['description'] ?? '',
        ];
    }

    private function packsDir(): string
    {
        $hostDir = resource_path('preset-packs');
        if (is_dir($hostDir) && ! empty(File::directories($hostDir))) {
            return $hostDir;
        }

        return __DIR__.'/../../../resources/preset-packs';
    }

    private function packDir(string $pack): string
    {
        return $this->packsDir().'/'.$pack;
    }

    private function isValidArtefactFile(\SplFileInfo $file): bool
    {
        return $file->getExtension() === 'md';
    }

    private function extractName(\SplFileInfo $file): string
    {
        return $file->getBasename('.md');
    }

    private function singularType(string $plural): string
    {
        return match ($plural) {
            'indicators' => 'indicator',
            'scorecards' => 'scorecard',
            'gauges' => 'gauge',
            'map-indicators' => 'map-indicator',
            'reports' => 'report',
            default => $plural,
        };
    }

    private function pluralType(string $singular): string
    {
        return match ($singular) {
            'indicator' => 'indicators',
            'scorecard' => 'scorecards',
            'gauge' => 'gauges',
            'map-indicator' => 'map-indicators',
            'report' => 'reports',
            default => $singular,
        };
    }
}
