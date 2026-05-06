import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';

class GetLabs {
  const GetLabs(this._repository);

  final LabsRepository _repository;

  Future<ApiResult<List<Lab>>> call() => _repository.getLabs();
}
