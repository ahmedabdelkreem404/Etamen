import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/workspaces/data/models/provider_operation_models.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';

abstract class WorkspaceRepository {
  Future<ApiResult<WorkspacesResponse>> getWorkspaces();

  Future<ApiResult<ProviderDashboard>> getProviderDashboard(int providerId);

  Future<ApiResult<ProviderOperationList>> getProviderOperationList(
    int providerId,
    String section,
  );

  Future<ApiResult<ProviderOperationItem>> getProviderOperationItem(
    int providerId,
    String section,
    int itemId,
  );

  Future<ApiResult<ProviderOperationItem>> runProviderOperationAction(
    int providerId,
    String section,
    int itemId,
    String action, {
    String? reason,
  });
}
