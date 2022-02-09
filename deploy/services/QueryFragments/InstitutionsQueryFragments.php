<?php

namespace App\Services\QueryFragments;

class InstitutionsQueryFragments
{
    public function getSqlFragments(array $filter) : array
    {
        if (!blank($filter['constituency'] ?? null)) {
            $selectColumns = ['i_ea_number AS area_code', 'i_ea_number AS area_name'];
            $whereConditions = ["i_constituency = '{$filter['constituency']}'"];

        }  elseif (!blank($filter['region'] ?? null)) {
            $selectColumns = ['i_constituency AS area_code', "i_constituency AS area_name"];
            $whereConditions = ["i_region = '{$filter['region']}'"];

        }else {
            $selectColumns = ['i_region AS area_code', 'i_region AS area_name'];
            $whereConditions = [];
        }
        return [$selectColumns, $whereConditions];
    }
}
