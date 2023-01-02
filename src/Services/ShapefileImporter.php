<?php

namespace Uneca\Chimera\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Shapefile\Shapefile;
use Shapefile\ShapefileReader;
use Throwable;

class ShapefileImporter
{
    protected $fromSrid;
    protected $toSrid;
    protected $debug;
    protected $debugInfo = [];

    const OPTIONS = [
        Shapefile::OPTION_SUPPRESS_M => true,
        Shapefile::OPTION_SUPPRESS_Z => true,
        Shapefile::OPTION_DBF_NULL_PADDING_CHAR => '*',
        Shapefile::OPTION_DBF_FORCE_ALL_CAPS => true,
        Shapefile::OPTION_DBF_IGNORED_FIELDS => ['ID', 'FID'],
        Shapefile::OPTION_POLYGON_CLOSED_RINGS_ACTION => Shapefile::ACTION_FORCE,
        //Shapefile::OPTION_POLYGON_OUTPUT_ORIENTATION => Shapefile::ORIENTATION_CLOCKWISE,
    ];

    public function __construct($fromSrid = 3857, $toSrid = 4326, $debug = false)
    {
        $this->fromSrid = $fromSrid;
        $this->toSrid = $toSrid;
        $this->debug = $debug;
    }

    private function castDataArray($shapefile, $attribs)
    {
        foreach($attribs as $key => $value) {
            switch($shapefile->getFieldType($key)) {
                case Shapefile::DBF_TYPE_NUMERIC:
                    $attribs[$key] = (int)$value;
                    break;
                case Shapefile::DBF_TYPE_FLOAT:
                    $attribs[$key] = (double)number_format((double)$value,2, '.', '');
            }
        }
        return $attribs;
    }

    public function import($filePath)
    {
        $collector = [];
        try {
            $shapefile = new ShapefileReader($filePath, self::OPTIONS);

            $totalRecords = $shapefile->getTotRecords();
            for ($i = 1; $i <= $totalRecords; $i++) {
                $shapefile->setCurrentRecord($i);
                try {
                    $feature = $shapefile->fetchRecord();

                    if ($feature->isDeleted()) {
                        continue;
                    }

                    $attribs = $this->castDataArray($shapefile, array_change_key_case($feature->getDataArray(), CASE_LOWER));
                    $geom = $feature->getWKT();
                    $geom = DB::raw(
                        //"ST_Transform(ST_GeomFromText('{$geom}', {$this->fromSrid}), {$this->toSrid})"
                        "ST_GeomFromText('{$geom}', 4326)"
                    );
                    array_push($collector, [
                        'attribs' => $attribs,
                        'geom' => $geom,
                    ]);

                } catch (Throwable $e) {
                    logger("Error fetching record $i: [Err Code: " . $e->getCode() . "] " . $e->getMessage());
                }
            }
        } catch (Throwable $e) {
            logger("Error instantiating ShapefileReader: [Err Code: " . $e->getCode() . "] " . $e->getMessage());
        }

        return $collector;
    }
}
