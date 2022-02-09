<?php

namespace App\Services\QueryFragments;

class HouseholdsQueryFragments
{
    public function getSqlFragments(array $filter) : array
    {
        if (!blank($filter['constituency'] ?? null)) {
            $selectColumns = ["CONCAT(LPAD(hh_region, 2, '0'),LPAD(hh_constituency,2,'0'),LPAD(hh_ea_number,3,'0')) AS area_code", "hh_ea_number AS area_name"];
            $whereConditions = ["hh_region = SUBSTR('{$filter['constituency']}',1,2)", "hh_constituency = SUBSTR('{$filter['constituency']}',3,2)"];

        }  elseif (!blank($filter['region'] ?? null)) {
            $selectColumns = ["CONCAT(LPAD(hh_region, 2, '0'),LPAD(hh_constituency,2,'0')) AS area_code", "hh_constituency AS area_name"];
            $whereConditions = ["hh_region = '{$filter['region']}'"];
        }else {
            $selectColumns = ["LPAD(hh_region, 2, '0') AS area_code", 'hh_region AS area_name'];
            $whereConditions = [];
        }
        return [$selectColumns, $whereConditions];
    }
    
    public function getMapQueryFragements($filter)
    {
        if (!blank($filter['constituency'] ?? null)) {
            $areaType = 'ea';
            $parentCodeCondition = "pcode = '{$filter['constituency']}'";

        } elseif (!blank($filter['region'] ?? null)) {
            $areaType = 'constituency';
            $parentCodeCondition = "pcode = '{$filter['region']}'";

        } else {
            $areaType = 'region';
            $parentCodeCondition = "pcode IS NULL";
        }
        return [$areaType, $parentCodeCondition];
    }
}
