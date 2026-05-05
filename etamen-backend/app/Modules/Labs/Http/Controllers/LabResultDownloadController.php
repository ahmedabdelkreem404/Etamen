<?php

namespace App\Modules\Labs\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use Illuminate\Support\Facades\Storage;

class LabResultDownloadController extends ApiController
{
    public function download(LabResult $result)
    {
        $this->authorize('download', $result);

        $file = $result->load('file')->file;

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
