<?php

namespace Uneca\Chimera\Mcp\Tools\Concerns;

use Illuminate\Support\Facades\File;

trait ResolvesStubPath
{
    private function resolveStubPath(string $relativePath): string
    {
        $path = resource_path("stubs/{$relativePath}");

        if (File::exists($path)) {
            return $path;
        }

        return __DIR__."/../../../../resources/stubs/{$relativePath}";
    }
}
