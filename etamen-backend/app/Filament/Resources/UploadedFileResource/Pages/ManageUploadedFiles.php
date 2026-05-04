<?php

namespace App\Filament\Resources\UploadedFileResource\Pages;

use App\Filament\Resources\UploadedFileResource;
use Filament\Resources\Pages\ManageRecords;

class ManageUploadedFiles extends ManageRecords
{
    protected static string $resource = UploadedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
