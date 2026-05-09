<?php

namespace App\Modules\Radiology\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Radiology\Infrastructure\Models\RadiologyResult;
use Illuminate\Support\Facades\Storage;

class RadiologyResultDownloadController extends ApiController
{
    public function download(RadiologyResult $result)
    {
        $this->authorize('download', $result);

        $file = $result->load('file')->file;

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
