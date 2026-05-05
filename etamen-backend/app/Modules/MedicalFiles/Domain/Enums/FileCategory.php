<?php

namespace App\Modules\MedicalFiles\Domain\Enums;

enum FileCategory: string
{
    case PaymentProof = 'payment_proof';
    case Prescription = 'prescription';
    case LabResult = 'lab_result';
    case MedicalReport = 'medical_report';
    case ProviderDocument = 'provider_document';
    case ProfileDocument = 'profile_document';
    case PharmacyProductImage = 'pharmacy_product_image';
    case MealPhoto = 'meal_photo';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
