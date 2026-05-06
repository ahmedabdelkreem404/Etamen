import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';
import 'package:etamen_app/features/ai_assistant/domain/repositories/ai_repository.dart';

class GetAiContextPreview {
  const GetAiContextPreview(this._repository);

  final AiRepository _repository;

  Future<ApiResult<AiContextPreview>> call() {
    return _repository.getContextPreview();
  }
}
