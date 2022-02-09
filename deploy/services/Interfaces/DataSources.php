<?php

namespace App\Services\Interfaces;

interface DataSources
{
    public static function getExpected(string $indicator) : callable;
    public static function getCollection(string $indicator) : callable;
    public static function filter(string $connection, array $filter) : array;
    public static function getAreaList(string $connection, string $areaType, ?string $parentArea) : array;
}
