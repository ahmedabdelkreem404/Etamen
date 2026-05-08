<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderDocumentVisibility: string
{
    case AdminOnly = 'admin_only';
    case PublicCertificate = 'public_certificate';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
