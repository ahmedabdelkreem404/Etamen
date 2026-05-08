<?php

namespace App\Modules\Providers\Domain\Enums;

enum ProviderDocumentType: string
{
    case NationalId = 'national_id';
    case MedicalLicense = 'medical_license';
    case SyndicateCard = 'syndicate_card';
    case Certificate = 'certificate';
    case TaxCard = 'tax_card';
    case CommercialRegister = 'commercial_register';
    case FacilityLicense = 'facility_license';
    case GymLicense = 'gym_license';
    case CoachCertificate = 'coach_certificate';
    case RadiologyLicense = 'radiology_license';
    case LabLicense = 'lab_license';
    case PharmacyLicense = 'pharmacy_license';
    case LegacyLicense = 'license';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function forcedAdminOnlyValues(): array
    {
        return [
            self::NationalId->value,
            self::TaxCard->value,
            self::CommercialRegister->value,
        ];
    }
}
