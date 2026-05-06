import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';

class UploadPrescription {
  const UploadPrescription(this._repository);

  final PharmacyRepository _repository;

  Future<ApiResult<PharmacyPrescription>> call(
    UploadPrescriptionRequest request,
  ) {
    return _repository.uploadPrescription(request);
  }
}
