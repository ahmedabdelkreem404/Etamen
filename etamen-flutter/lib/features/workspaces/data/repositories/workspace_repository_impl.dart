import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/workspaces/data/datasources/workspace_remote_data_source.dart';
import 'package:etamen_app/features/workspaces/data/models/provider_operation_models.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';
import 'package:etamen_app/features/workspaces/domain/repositories/workspace_repository.dart';

class WorkspaceRepositoryImpl implements WorkspaceRepository {
  const WorkspaceRepositoryImpl(this._remoteDataSource);

  final WorkspaceRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<WorkspacesResponse>> getWorkspaces() {
    return _remoteDataSource.getWorkspaces();
  }

  @override
  Future<ApiResult<ProviderDashboard>> getProviderDashboard(int providerId) {
    return _remoteDataSource.getProviderDashboard(providerId);
  }

  @override
  Future<ApiResult<ProviderOperationList>> getProviderOperationList(
    int providerId,
    String section,
  ) {
    return _remoteDataSource.getProviderOperationList(providerId, section);
  }

  @override
  Future<ApiResult<ProviderOperationItem>> getProviderOperationItem(
    int providerId,
    String section,
    int itemId,
  ) {
    return _remoteDataSource.getProviderOperationItem(
      providerId,
      section,
      itemId,
    );
  }

  @override
  Future<ApiResult<ProviderOperationItem>> runProviderOperationAction(
    int providerId,
    String section,
    int itemId,
    String action, {
    String? reason,
  }) {
    return _remoteDataSource.runProviderOperationAction(
      providerId,
      section,
      itemId,
      action,
      reason: reason,
    );
  }
}
