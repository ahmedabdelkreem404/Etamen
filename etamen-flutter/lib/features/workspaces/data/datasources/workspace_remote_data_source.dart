import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
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

  Map<String, dynamic> _asMap(Object? raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }
}
