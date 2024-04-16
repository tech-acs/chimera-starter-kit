<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DownloadIndicatorTemplates extends Command
{
    public const LATEST_RELEASE = '/releases/latest';
    public const RELEASE_BY_TAG_NAME = '/releases/tags/';

    protected ?string $tag = null;

    protected $signature = 'chimera:download-indicator-templates
                            {--tag= : The version of the indicator templates to install. If not provided, the latest release will be installed.}
                            {--force : Whether to overwrite existing files}';

    protected $description = 'Download indicator templates from the online repository';


    public function handle()
    {
        $this->components->info("Download and extract indicator templates from online repository");
        $isDownloaded = false;
        $this->components->task('Downloading', function () use (&$isDownloaded) {
            $isDownloaded = $this->downloadIndicatorTemplates();
            return $isDownloaded;
        });
        if ($isDownloaded) {
            echo($this->downloadPath());
            $this->components->task('Extracting', function () {
                return $this->extractIndicatorTemplates();
            });
        }
        return Command::SUCCESS;
    }

    protected function getDownloadUrl(): string
    {
        if ($this->option('tag')) {
            // -> /repos/{owner}/{repo}/releases/tags/{tag}
            $repo = config('chimera.indicator_template.repository_url') . self::RELEASE_BY_TAG_NAME . $this->option('tag');
        } else {
            // -> /repos/{owner}/{repo}/releases/latest
            $repo = config('chimera.indicator_template.repository_url') . self::LATEST_RELEASE;
        }
        try {
            $response = Http::get($repo);
            if ($response->successful()) {
                return $response->json()['zipball_url'] ?? '';
            }
        } catch (\Exception $exception) {
            //
        }
        return '';
    }

    protected function downloadPath(): string
    {
        return Storage::disk('indicator_templates')
            ->path('chimera-indicator-templates.zip');
    }

    protected function downloadIndicatorTemplates(): bool
    {
        $zipBallUrl = $this->getDownloadUrl();
        if (! empty($zipBallUrl)) {
            try {
                $response = Http::sink($this->downloadPath())
                    ->get($zipBallUrl);
                return $response->successful();
            } catch (\Exception $exception) {
                //
            }
        }
        return false;
    }

    protected function extractIndicatorTemplates(): bool
    {
        try {
            $zip = new ZipArchive();
            $zip->open($this->downloadPath());
            $zip->extractTo(Storage::disk('indicator_templates')->path(''));
            $rootFolder = $zip->getNameIndex(0);
            $zip->close();
            // Move the files to the root folder
            $files = Storage::disk('indicator_templates')->allFiles($rootFolder);
            foreach ($files as $file) {
                Storage::disk('indicator_templates')->move($file, str_replace($rootFolder, '', $file));
            }
            Storage::disk('indicator_templates')->deleteDirectory($rootFolder);
            return true;
        } catch (\Exception $exception) {
            //
        }
        return false;
    }
}
