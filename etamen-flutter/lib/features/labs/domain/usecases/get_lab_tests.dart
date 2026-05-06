import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class GetLabTests {
  const GetLabTests(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<List<LabTest>>> call(int labId) =>
      _repository.getTests(labId);
}
