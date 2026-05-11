import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/workspaces/data/models/provider_operation_models.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';

class WorkspaceRemoteDataSource {
  const WorkspaceRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<WorkspacesResponse>> getWorkspaces() {
    return _client.get<WorkspacesResponse>(
      ApiEndpoints.workspaces,
      parser: (raw) => WorkspacesResponse.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<ProviderDashboard>> getProviderDashboard(int providerId) {
    return _client.get<ProviderDashboard>(
      ApiEndpoints.providerWorkspaceDashboard(providerId),
      parser: (raw) => ProviderDashboard.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<ProviderOperationList>> getProviderOperationList(
    int providerId,
    String section,
  ) {
    return _client.get<ProviderOperationList>(
      ApiEndpoints.providerWorkspaceOperation(providerId, section),
      parser: (raw) => ProviderOperationList.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<ProviderOperationItem>> getProviderOperationItem(
    int providerId,
    String section,
    int itemId,
  ) {
    return _client.get<ProviderOperationItem>(
      ApiEndpoints.providerWorkspaceOperationItem(providerId, section, itemId),
      parser: (raw) => ProviderOperationItem(raw: _asMap(raw)),
    );
  }

  Future<ApiResult<ProviderOperationItem>> runProviderOperationAction(
    int providerId,
    String section,
    int itemId,
    String action, {
    String? reason,
  }) {
    final trimmedReason = reason?.trim();
    return _client.post<ProviderOperationItem>(
      ApiEndpoints.providerWorkspaceOperationAction(
        providerId,
        section,
        itemId,
        action,
      ),
      data: trimmedReason == null || trimmedReason.isEmpty
          ? null
          : {'reason': trimmedReason},
      parser: (raw) => ProviderOperationItem(raw: _asMap(raw)),
    );
  }

  Map<String, dynamic> _asMap(Object? raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
