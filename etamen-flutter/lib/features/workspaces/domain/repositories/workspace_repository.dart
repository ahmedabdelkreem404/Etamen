import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';

abstract class WorkspaceRepository {
  Future<ApiResult<WorkspacesResponse>> getWorkspaces();

  Future<ApiResult<ProviderDashboard>> getProviderDashboard(int providerId);
}
