<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DownloadIndicatorTemplates extends Command
{
    public const LATEST_RELEASE_API = '/releases/latest';
    public const RELEASE_BY_TAG_API = '/releases/tags/';
    protected ?string $tag = null;

    protected string $downloadUrl;

    protected $signature = 'chimera:download-indicator-templates
                            {--tag= : The version of the indicator templates to install. If not provided, the latest release will be installed.}
                            {--force : Whether to overwrite existing files}';

    protected $description = 'Download indicator templates from the repository to the local storage';


    public function handle()
    {
        $downloadPath = $this->downloadIndicatorTemplates();

        $this->extractIndicatorTemplates($downloadPath);

        $this->info('Installed indicator templates successfully.');

        return Command::SUCCESS;
    }

    protected function getReleaseUrl(): string
    {
        $repository_url = config('chimera.indicator_template.repository_url', 'https://api.github.com/repos/tech-acs/chimera-indicator-templates');

        if ($this->option('tag')) {
            return $repository_url . self::RELEASE_BY_TAG_API . $this->option('tag');
        } else {
            return $repository_url . self::LATEST_RELEASE_API;
        }
    }

    protected function getDownloadUrl(): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36'
        ])->get($this->getReleaseUrl());
        $release = $response->object();
        $this->downloadUrl = $release->zipball_url;
        return $this->downloadUrl;
    }

    protected function downloadIndicatorTemplates(): string
    {
        $this->info('Downloading indicator templates...');
        $downloadUrl = $this->getDownloadUrl();
        $downloadPath = Storage::disk('indicator_templates')->path('chimera-indicator-templates.zip');
        Http::sink($downloadPath)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36'
        ])->get($downloadUrl);

        $this->info('Downloaded indicator templates successfully.');
        return $downloadPath;
    }

    protected function extractIndicatorTemplates($downloadPath)
    {
        $destination_path = Storage::disk('indicator_templates')->path('');

        $this->info('Extracting indicator templates...');
        //First, extract the zip file
        $zip = new ZipArchive();
        $zip->open($downloadPath);
        $zip->extractTo($destination_path);
        $rootFolder = $zip->getNameIndex(0);
        $zip->close();
        //Then, move the files to the root folder
        $files = Storage::disk('indicator_templates')->allFiles($rootFolder);
        $progress = $this->output->createProgressBar(count($files));
        foreach ($files as $file) {
            $newFile = str_replace($rootFolder, '', $file);
            Storage::disk('indicator_templates')->move($file, $newFile);
            $progress->advance();
        }
        $progress->finish();
        $this->newLine();
        //Finally, delete the root folder
        Storage::disk('indicator_templates')->deleteDirectory($rootFolder);
        $this->info('Extracted indicator templates successfully.');
    }
}
