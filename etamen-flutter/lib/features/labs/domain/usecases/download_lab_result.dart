import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class DownloadLabResult {
  const DownloadLabResult(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<LabResultDownload>> call(int resultId) {
    return _repository.downloadResult(resultId);
  }
}
