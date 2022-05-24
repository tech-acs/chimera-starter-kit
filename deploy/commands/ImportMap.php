<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportMap extends Command
{
    protected $signature = 'chimera:import-map
                            {path : Path to directory containing all your shapefiles}';

    protected $description = 'Import shapefiles into the database (maps table)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $pathToShapefiles = rtrim($this->argument('path'), '/') . '/';
        $simplificationFactors = config('chimera.map.shape_simplification', []);
        $shapefiles = collect($simplificationFactors)->map(function ($factor, $type) use ($pathToShapefiles) {
            return glob($pathToShapefiles . $type . '/*.shp');
        });

        foreach ($shapefiles as $areaType => $areaShapefiles) {
            $this->info("\nImporting " . Str::of($areaType)->plural()->upper());
            foreach ($areaShapefiles as $shapefile) {
                $this->info("File: $shapefile");
                $dbConnection = vsprintf(
                    'host=%s dbname=%s user=%s password=%s',
                    [env('DB_HOST'), env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD')]
                );
                $command = 'ogr2ogr -f PostgreSQL PG:"' . $dbConnection .  '" "' .
                    $shapefile . '" -nln "maps" -sql "select \'' . $areaType .
                    "' AS area_type, code, name, pcode, geometry AS geom from '" .
                    basename($shapefile, '.shp') .
                    "'\" -dialect sqlite -t_srs EPSG:4326 -simplify {$simplificationFactors[$areaType]} -skipfailures";
                exec($command, $output, $retval);
            }
        }
    }
}
