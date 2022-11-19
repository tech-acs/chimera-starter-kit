<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class InstallIndicatorTemplates extends Command
{
    const LATEST_RELEASE_API = '/releases/latest';
    const RELEASE_BY_TAG_API = '/releases/tags/';
    protected ?string $tag = null;

    protected string $downloadUrl;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chimera:install-indicator-templates
                            {--tag= : The version of the indicator templates to install. If not provided, the latest release will be installed.}
                            {--force : Whether to overwrite existing files}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install indicator templates from the repository to the local storage';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       
        $downloadPath = $this->downloadIndicatorTemplates();

        $this->extractIndicatorTemplates($downloadPath);

        $this->info('Installed indicator templates successfully.');

        return Command::SUCCESS;
    }

    protected function getReleaseUrl(): string {
        $url = config('chimera.indicator_template.url','https://api.github.com/repos/tech-acs/chimera-indicator-templates/');

        if( $this->option('tag')){
            $url .= self::RELEASE_BY_TAG_API. $this->option('tag');
        }
        else{
            $url .= self::LATEST_RELEASE_API;
        }
        return $url;

    }

   

    protected function getDownloadUrl(): string{
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
        $downloadPath =Storage::disk('indicator_templates')->path('chimera-indicator-templates.zip');
        Http::sink($downloadPath)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36'
        ])->get($downloadUrl);

        $this->info('Downloaded indicator templates successfully.');
        return $downloadPath;
    }

    protected function extractIndicatorTemplates($downloadPath)
    {
        //Todo: this is a hack, need to find a better way to extract the zip file and remove the root folder
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
        foreach ($files as $file) {
            $newFile = str_replace($rootFolder, '', $file);
            Storage::disk('indicator_templates')->move($file, $newFile);
            $this->info('Extracted '.$newFile);
        }
        //Finally, delete the root folder
        Storage::disk('indicator_templates')->deleteDirectory($rootFolder);
        $this->info('Extracted indicator templates successfully.');
    }
    
}
