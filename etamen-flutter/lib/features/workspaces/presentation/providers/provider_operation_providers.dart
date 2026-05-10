import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/features/workspaces/data/models/provider_operation_models.dart';
import 'package:etamen_app/features/workspaces/domain/repositories/workspace_repository.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/workspace_providers.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class ProviderOperationArgs {
  const ProviderOperationArgs({
    required this.providerId,
    required this.section,
  });

  final int providerId;
  final String section;

  @override
  bool operator ==(Object other) {
    return other is ProviderOperationArgs &&
        other.providerId == providerId &&
        other.section == section;
  }

  @override
  int get hashCode => Object.hash(providerId, section);
}

class ProviderOperationItemArgs extends ProviderOperationArgs {
  const ProviderOperationItemArgs({
    required super.providerId,
    required super.section,
    required this.itemId,
  });

  final int itemId;

  @override
  bool operator ==(Object other) {
    return other is ProviderOperationItemArgs &&
        other.providerId == providerId &&
        other.section == section &&
        other.itemId == itemId;
  }

  @override
  int get hashCode => Object.hash(providerId, section, itemId);
}

class ProviderOperationListState {
  const ProviderOperationListState({
    this.items = const [],
    this.isLoading = false,
    this.error,
  });

  final List<ProviderOperationItem> items;
  final bool isLoading;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  ProviderOperationListState copyWith({
    List<ProviderOperationItem>? items,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return ProviderOperationListState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final providerOperationListControllerProvider = StateNotifierProvider
    .autoDispose
    .family<
      ProviderOperationListController,
      ProviderOperationListState,
      ProviderOperationArgs
    >((ref, args) {
      return ProviderOperationListController(
        args,
        ref.watch(workspaceRepositoryProvider),
      )..load();
    });

class ProviderOperationListController
    extends StateNotifier<ProviderOperationListState> {
  ProviderOperationListController(this.args, this._repository)
    : super(const ProviderOperationListState());

  final ProviderOperationArgs args;
  final WorkspaceRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getProviderOperationList(
      args.providerId,
      args.section,
    );
    state = result.when(
      success: (list) =>
          state.copyWith(items: list.items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

class ProviderOperationDetailsState {
  const ProviderOperationDetailsState({
    this.item,
    this.isLoading = false,
    this.isSubmitting = false,
    this.error,
  });

  final ProviderOperationItem? item;
  final bool isLoading;
  final bool isSubmitting;
  final ApiError? error;

  ProviderOperationDetailsState copyWith({
    ProviderOperationItem? item,
    bool? isLoading,
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return ProviderOperationDetailsState(
      item: item ?? this.item,
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final providerOperationDetailsControllerProvider = StateNotifierProvider
    .autoDispose
    .family<
      ProviderOperationDetailsController,
      ProviderOperationDetailsState,
      ProviderOperationItemArgs
    >((ref, args) {
      return ProviderOperationDetailsController(
        args,
        ref.watch(workspaceRepositoryProvider),
      )..load();
    });

class ProviderOperationDetailsController
    extends StateNotifier<ProviderOperationDetailsState> {
  ProviderOperationDetailsController(this.args, this._repository)
    : super(const ProviderOperationDetailsState());

  final ProviderOperationItemArgs args;
  final WorkspaceRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getProviderOperationItem(
      args.providerId,
      args.section,
      args.itemId,
    );
    state = result.when(
      success: (item) =>
          state.copyWith(item: item, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<bool> runAction(String action) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _repository.runProviderOperationAction(
      args.providerId,
      args.section,
      args.itemId,
      action,
    );
    return result.when(
      success: (item) {
        state = state.copyWith(
          item: item,
          isSubmitting: false,
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}
