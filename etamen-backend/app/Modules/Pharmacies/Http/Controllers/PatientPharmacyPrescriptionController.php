<?php

namespace App\Modules\Pharmacies\Http\Controllers;

use App\Core\Http\ApiController;
use App\Modules\Pharmacies\Application\Services\PharmacyPrescriptionService;
use App\Modules\Pharmacies\Http\Requests\UploadPharmacyPrescriptionRequest;
use App\Modules\Pharmacies\Http\Resources\PharmacyPrescriptionResource;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyPrescription;
use Illuminate\Support\Facades\Storage;

class PatientPharmacyPrescriptionController extends ApiController
{
    public function __construct(private readonly PharmacyPrescriptionService $prescriptionService) {}

    public function store(UploadPharmacyPrescriptionRequest $request)
    {
        $prescription = $this->prescriptionService->upload(
            $request->user(),
            $request->file('file'),
            $request->validated(),
        );

        return $this->success(new PharmacyPrescriptionResource($prescription), 'Prescription uploaded.', 201);
    }

    public function download(PharmacyPrescription $prescription)
    {
        $this->authorize('view', $prescription);

        $file = $prescription->load('uploadedFile')->uploadedFile;

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
