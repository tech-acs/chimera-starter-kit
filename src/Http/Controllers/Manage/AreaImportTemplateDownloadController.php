<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Uneca\Chimera\Services\AreaTree;

class AreaImportTemplateDownloadController extends Controller
{
    public function __invoke()
    {
        $columnHeaders = [];
        foreach ((new AreaTree)->hierarchies as $level) {
            $columnHeaders[] = "{$level}_name";
            $columnHeaders[] = "{$level}_code";
        }
        return SimpleExcelWriter::streamDownload('area_import_template.xlsx')
            ->addHeader($columnHeaders)
            ->toBrowser();
    }
}
