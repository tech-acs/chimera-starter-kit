<?php

namespace Uneca\Chimera\Commands;

use Illuminate\Console\GeneratorCommand;

class ChimeraArtefactGenerator extends GeneratorCommand
{
    protected $signature = 'chimera:make-artefact {name} {--stub} {--namespace}';
    protected $description = 'Create a new dashboard artefact';

    protected string $artefactName;
    protected string $stub;
    protected string $relativeNamespace;

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . $this->relativeNamespace;
    }

    protected function getStub()
    {
        return $this->stub;
    }

    public function handle()
    {
        $this->artefactName = $this->argument('name');
        $this->stub = $this->option('stub');
        $this->relativeNamespace = $this->option('namespace');

        $className = $this->qualifyClass($this->artefactName);
        $path = $this->getPath($className);
        $this->makeDirectory($path);
        $content = $this->buildClass($className);

        $result = $this->files->put($path, $content);

        if ($result === false) {
            $this->error('File could not be written.');
            return self::FAILURE;
        }
        $this->info('File created successfully.');
        return self::SUCCESS;
    }
}
