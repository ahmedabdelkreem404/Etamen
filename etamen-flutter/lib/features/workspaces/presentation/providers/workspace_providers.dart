import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/workspaces/data/datasources/workspace_remote_data_source.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';
import 'package:etamen_app/features/workspaces/data/repositories/workspace_repository_impl.dart';
import 'package:etamen_app/features/workspaces/data/storage/workspace_selection_storage.dart';
import 'package:etamen_app/features/workspaces/domain/repositories/workspace_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final workspaceSelectionStorageProvider = Provider<WorkspaceSelectionStorage>((
  ref,
) {
  return WorkspaceSelectionStorage();
});

final workspaceRemoteDataSourceProvider = Provider<WorkspaceRemoteDataSource>((
  ref,
) {
  return WorkspaceRemoteDataSource(ref.watch(apiClientProvider));
});

final workspaceRepositoryProvider = Provider<WorkspaceRepository>((ref) {
  return WorkspaceRepositoryImpl(ref.watch(workspaceRemoteDataSourceProvider));
});

class WorkspaceState {
  const WorkspaceState({
    this.response,
    this.selectedKey,
    this.isLoading = false,
    this.error,
  });

  final WorkspacesResponse? response;
  final String? selectedKey;
  final bool isLoading;
  final ApiError? error;

  List<WorkspaceSummary> get workspaces =>
      response?.availableWorkspaces ?? const [];

  WorkspaceSummary? get selectedWorkspace {
    final current = response?.workspaceByKey(selectedKey);
    if (current != null) return current;
    return response?.workspaceByKey(response?.defaultWorkspace) ??
        (workspaces.isNotEmpty ? workspaces.first : null);
  }

  bool get hasMultipleWorkspaces => workspaces.length > 1;

  WorkspaceState copyWith({
    WorkspacesResponse? response,
    String? selectedKey,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
    bool clearSelected = false,
  }) {
    return WorkspaceState(
      response: response ?? this.response,
      selectedKey: clearSelected ? null : selectedKey ?? this.selectedKey,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final workspaceControllerProvider =
    StateNotifierProvider<WorkspaceController, WorkspaceState>((ref) {
      return WorkspaceController(
        ref.watch(workspaceRepositoryProvider),
        ref.watch(workspaceSelectionStorageProvider),
      );
    });

class WorkspaceController extends StateNotifier<WorkspaceState> {
  WorkspaceController(this._repository, this._storage)
    : super(const WorkspaceState());

  final WorkspaceRepository _repository;
  final WorkspaceSelectionStorage _storage;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final storedKey = await _storage.readSelectedWorkspaceKey();
    final result = await _repository.getWorkspaces();
    await result.when(
      success: (response) async {
        final selected =
            response.workspaceByKey(storedKey)?.key ??
            response.workspaceByKey(response.defaultWorkspace)?.key ??
            (response.availableWorkspaces.isNotEmpty
                ? response.availableWorkspaces.first.key
                : null);
        if (selected != null) {
          await _storage.saveSelectedWorkspaceKey(selected);
        }
        state = state.copyWith(
          response: response,
          selectedKey: selected,
          isLoading: false,
          clearError: true,
        );
      },
      failure: (failure) async {
        state = state.copyWith(isLoading: false, error: failure.error);
      },
    );
  }

  Future<void> switchTo(String key) async {
    final workspace = state.response?.workspaceByKey(key);
    if (workspace == null) return;
    await _storage.saveSelectedWorkspaceKey(workspace.key);
    state = state.copyWith(selectedKey: workspace.key, clearError: true);
  }

  Future<void> clearSelection() async {
    await _storage.clearSelectedWorkspaceKey();
    state = const WorkspaceState();
  }
}

class ProviderDashboardState {
  const ProviderDashboardState({
    this.dashboard,
    this.isLoading = false,
    this.error,
  });

  final ProviderDashboard? dashboard;
  final bool isLoading;
  final ApiError? error;

  ProviderDashboardState copyWith({
    ProviderDashboard? dashboard,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return ProviderDashboardState(
      dashboard: dashboard ?? this.dashboard,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final providerDashboardControllerProvider = StateNotifierProvider.autoDispose
    .family<ProviderDashboardController, ProviderDashboardState, int>((
      ref,
      providerId,
    ) {
      return ProviderDashboardController(
        providerId,
        ref.watch(workspaceRepositoryProvider),
      )..load();
    });

class ProviderDashboardController
    extends StateNotifier<ProviderDashboardState> {
  ProviderDashboardController(this.providerId, this._repository)
    : super(const ProviderDashboardState());

  final int providerId;
  final WorkspaceRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getProviderDashboard(providerId);
    state = result.when(
      success: (dashboard) => state.copyWith(
        dashboard: dashboard,
        isLoading: false,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}
