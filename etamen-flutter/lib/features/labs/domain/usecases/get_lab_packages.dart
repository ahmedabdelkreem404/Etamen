import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class GetLabPackages {
  const GetLabPackages(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<List<LabPackage>>> call(int labId) {
    return _repository.getPackages(labId);
  }
}
