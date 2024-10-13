<?php

namespace Uneca\Chimera\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
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

    public function import($filePath): LazyCollection
    {
        ini_set('memory_limit', '1024M');

        try {
            $shapefile = new ShapefileReader($filePath, self::OPTIONS);
            $totalRecords = $shapefile->getTotRecords();
            return LazyCollection::make(function () use ($shapefile, $totalRecords) {
                for ($i = 1; $i <= $totalRecords; $i++) {
                    $shapefile->setCurrentRecord($i);
                    try {
                        $feature = $shapefile->fetchRecord();

                        if ($feature->isDeleted()) {
                            continue;
                        }

                        $attribs = $this->castDataArray($shapefile, array_change_key_case($feature->getDataArray(), CASE_LOWER));
                        $wkt = $feature->getWKT();
                        $result = DB::select("SELECT ST_GeomFromText('{$wkt}', 4326) AS geom");

                        yield [
                            'attribs' => $attribs,
                            'geom' => $result[0]?->geom ?? null,
                        ];

                    } catch (Throwable $e) {
                        logger("Error fetching record $i: [Err Code: " . $e->getCode() . "] " . $e->getMessage());
                    }
                }
            });

        } catch (Throwable $e) {
            logger("Error instantiating ShapefileReader: [Err Code: " . $e->getCode() . "] " . $e->getMessage());
        }
    }

    public function sample($filePath): ?array
    {
        ini_set('memory_limit', '1024M');

        try {
            $shapefile = new ShapefileReader($filePath, self::OPTIONS);
            $totalRecords = $shapefile->getTotRecords();
            $sampleFeatureNotAcquired = true; $currentRecord = 1; $sample = null;
            while ($sampleFeatureNotAcquired && ($currentRecord <= $totalRecords)) {
                $shapefile->setCurrentRecord($currentRecord);
                try {
                    $sampleFeature = $shapefile->fetchRecord();

                    if ($sampleFeature->isDeleted()) {
                        $currentRecord++;
                        continue;
                    }

                    $attribs = $this->castDataArray($shapefile, array_change_key_case($sampleFeature->getDataArray(), CASE_LOWER));
                    $wkt = $sampleFeature->getWKT();
                    $result = DB::select("SELECT ST_GeomFromText('{$wkt}', 4326) AS geom");

                    $sample = [
                        'attribs' => $attribs,
                        'geom' => $result[0]?->geom ?? null,
                    ];
                    $sampleFeatureNotAcquired = false;

                } catch (Throwable $e) {
                    logger("Error fetching record $currentRecord: [Err Code: " . $e->getCode() . "] " . $e->getMessage());
                }
            }
            return $sample;
        } catch (Throwable $e) {
            logger("Error instantiating ShapefileReader: [Err Code: " . $e->getCode() . "] " . $e->getMessage());
        }
        return null;
    }
}
