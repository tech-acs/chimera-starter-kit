<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;


class Dockerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chimera:dockerize
                 {--with= : The services that should be included in the installation}
                 ';
    protected $dirStubs = __DIR__ .'/../../docker/stub/';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a docker-compose.yml and neccessary file for the application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $services = $this->getServices();
        $this->buildDockerComposer($services);
        return 0;
    }
        
    protected function createDirectory($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    protected function getServices(){
        
        if($this->option('with')){
            $services = $this->option('with') == 'none' ? [] : explode(',', $this->option('with'));
        } elseif ($this->option('no-interaction')) {
            $services = [];
        } else {
            $services = $this->askForAdditionalServices();
        }
        return collect($services);

    }
    private function copyFilesInDir(string $srcDir, string $destDir, string $fileType = '*.*')
    {
        $fs = new FileSystem;
        $fs->ensureDirectoryExists($destDir);
        foreach (glob("$srcDir/$fileType") as $file) {
            $fs->copy($file, "$destDir/" . basename($file));
        }
    }

    protected function askForAdditionalServices(){
        if($this->choice("Would you like to add postgres database container?", ['y' => 'Yes', 'n' => 'No'], 'y') == 'y'){
            $services = ['postgres'];
        } else {
            $services = [];
        }
        if($this->choice("Would you like to add redis (cachin) container?", ['y' => 'Yes', 'n' => 'No'], 'y') == 'y'){
            $services = array_merge($services, ['redis']);
        }
        return $services;
    }

    protected function copyConfigFiles(){
        
    }
    
    protected function buildDockerComposer(Collection $options){
        // get docker-compose
        $dockerComposer = rtrim(file_get_contents($this->dirStubs.'docker-compose.stub'));
        
        // get services
        $services = $options->map(function($service){
            $this->info("Building service: $service");
            return rtrim(file_get_contents($this->dirStubs.'chimera.'.$service.'.stub'));
        })->implode("");  
        // get dependencies
        $dependencies = $options->map(function($service){
            $this->info("Adding $service to dependencies");
            return "            - chimera.$service";
        })->whenNotEmpty(function($collection){
            return $collection->prepend('depends_on:');
        })->implode("\n");
        // get volumes
        if($options->contains('postgres')){
            $volumes = "volumes:\n        chimera-db:\n            driver: local";
        }else {
            $volumes = "";
        }
        // set stubs
        $dockerComposer = str_replace('{{services}}',$services,$dockerComposer);
        $dockerComposer = str_replace('{{dependencies}}',empty($dependencies)?'':$dependencies,$dockerComposer);
        $dockerComposer = str_replace('{{volumes}}',$volumes,$dockerComposer);
        // copy config files
        $this->copyFilesInDir(__DIR__ . '/../../docker/runtimes/config', base_path('runtimes/config'));
        $this->comment('Copied  configuration files');
        copy(__DIR__ . '/../../docker/runtimes/entrypoint.sh', base_path('runtimes/entrypoint.sh'));
        copy(__DIR__ . '/../../docker/runtimes/Dockerfile', base_path('runtimes/DockerFile'));        
        $this->comment('Copied  Docker files');

        copy(__DIR__ . '/../../docker/.env.docker', base_path('.env.docker'));
        $this->comment('Copied .env.docker');
        
        // write docker-compose
        file_put_contents(base_path('docker-compose.yml'), $dockerComposer);

    }

}
