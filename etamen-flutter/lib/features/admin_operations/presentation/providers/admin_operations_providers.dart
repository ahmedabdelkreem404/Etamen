import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/admin_operations/data/admin_operation_models.dart';
import 'package:etamen_app/features/admin_operations/data/admin_operations_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final adminOperationsRepositoryProvider = Provider<AdminOperationsRepository>((
  ref,
) {
  return AdminOperationsRepository(ref.watch(apiClientProvider));
});

final adminDashboardProvider = FutureProvider.autoDispose<AdminDashboard>((
  ref,
) async {
  final result = await ref.watch(adminOperationsRepositoryProvider).dashboard();
  return _unwrap(result);
});

final adminListProvider = FutureProvider.autoDispose
    .family<AdminListResponse, String>((ref, section) async {
      final result = await ref
          .watch(adminOperationsRepositoryProvider)
          .adminList(section);
      return _unwrap(result);
    });

class AdminDetailParams {
  const AdminDetailParams({required this.section, required this.id});

  final String section;
  final int id;

  @override
  bool operator ==(Object other) {
    return other is AdminDetailParams &&
        other.section == section &&
        other.id == id;
  }

  @override
  int get hashCode => Object.hash(section, id);
}

final adminDetailsProvider = FutureProvider.autoDispose
    .family<AdminListItem, AdminDetailParams>((ref, params) async {
      final result = await ref
          .watch(adminOperationsRepositoryProvider)
          .adminDetails(params.section, params.id);
      return _unwrap(result);
    });

final supportTicketsProvider = FutureProvider.autoDispose<AdminListResponse>((
  ref,
) async {
  final result = await ref
      .watch(adminOperationsRepositoryProvider)
      .supportTickets();
  return _unwrap(result);
});

T _unwrap<T>(ApiResult<T> result) {
  return result.when(
    success: (data) => data,
    failure: (failure) => throw failure.error,
  );
}

String errorMessage(Object error) {
  if (error is ApiError) return error.message;
  return 'تعذر تحميل البيانات';
}
