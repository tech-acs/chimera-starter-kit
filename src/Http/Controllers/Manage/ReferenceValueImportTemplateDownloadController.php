<?php

namespace Uneca\Chimera\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ReferenceValueImportTemplateDownloadController extends Controller
{
    public function __invoke()
    {
        return SimpleExcelWriter::streamDownload('reference_value_import_template.xlsx')
            ->addHeader(['Path', 'Indicator 1', 'Indicator 2', 'Indicator n'])
            ->toBrowser();
    }
}
