<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ShpToDb extends Command
{
    protected $signature = 'chimera:shp-to-db';

    protected $description = 'Load all shapefiles into the database (maps table)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $maps = config('chimera.maps', []);
        $shapefiles = collect($maps)->map(function ($item, $key) {
            return glob(base_path("shapefiles/$key/") . '*.shp');
        });
        $tolerances = $maps;

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
                    "'\" -dialect sqlite -t_srs EPSG:4326 -simplify {$tolerances[$areaType]} -skipfailures";
                exec($command, $output, $retval);
            }
        }
    }
}
