<?php

namespace App\Modules\Radiology\Domain\Enums;

enum RadiologyResultType: string
{
    case ReportPdf = 'report_pdf';
    case ImageFile = 'image_file';
    case DicomLink = 'dicom_link';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
