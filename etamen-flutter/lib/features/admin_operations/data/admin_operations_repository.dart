import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/admin_operations/data/admin_operation_models.dart';

class AdminOperationsRepository {
  const AdminOperationsRepository(this._client);

  final ApiClient _client;

  Future<ApiResult<AdminDashboard>> dashboard() {
    return _client.get<AdminDashboard>(
      ApiEndpoints.adminOperationsDashboard,
      parser: (raw) => AdminDashboard.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListResponse>> adminList(String section) {
    final path = section == 'audit-log'
        ? ApiEndpoints.adminOperationsAuditLog
        : ApiEndpoints.adminOperationsList(section);
    return _client.get<AdminListResponse>(
      path,
      parser: (raw) => AdminListResponse.fromJson(raw),
    );
  }

  Future<ApiResult<AdminListItem>> adminDetails(String section, int id) {
    return _client.get<AdminListItem>(
      ApiEndpoints.adminOperationsItem(section, id),
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListItem>> adminAction(
    String section,
    int id,
    String action, {
    Map<String, dynamic>? data,
  }) {
    return _client.post<AdminListItem>(
      ApiEndpoints.adminOperationsAction(section, id, action),
      data: data ?? const {},
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListResponse>> supportTickets() {
    return _client.get<AdminListResponse>(
      ApiEndpoints.supportTickets,
      parser: (raw) => AdminListResponse.fromJson(raw),
    );
  }

  Future<ApiResult<AdminListItem>> supportTicket(int id) {
    return _client.get<AdminListItem>(
      ApiEndpoints.supportTicket(id),
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListItem>> createSupportTicket({
    required String category,
    required String subject,
    required String description,
    int? providerId,
  }) {
    return _client.post<AdminListItem>(
      ApiEndpoints.supportTickets,
      data: {
        'category': category,
        'subject': subject,
        'description': description,
        if (providerId != null) 'provider_id': providerId,
      },
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListItem>> replySupportTicket(int id, String message) {
    return _client.post<AdminListItem>(
      ApiEndpoints.supportTicketMessages(id),
      data: {'message': message},
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListResponse>> refunds() {
    return _client.get<AdminListResponse>(
      ApiEndpoints.refunds,
      parser: (raw) => AdminListResponse.fromJson(raw),
    );
  }

  Future<ApiResult<AdminListItem>> createRefund({
    required String reason,
    required double amount,
  }) {
    return _client.post<AdminListItem>(
      ApiEndpoints.refunds,
      data: {'reason': reason, 'amount': amount, 'currency': 'EGP'},
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Future<ApiResult<AdminListResponse>> disputes() {
    return _client.get<AdminListResponse>(
      ApiEndpoints.disputes,
      parser: (raw) => AdminListResponse.fromJson(raw),
    );
  }

  Future<ApiResult<AdminListItem>> createDispute({
    required String reason,
    int? providerId,
  }) {
    return _client.post<AdminListItem>(
      ApiEndpoints.disputes,
      data: {
        'reason': reason,
        if (providerId != null) 'provider_id': providerId,
      },
      parser: (raw) => AdminListItem.fromJson(_asMap(raw)),
    );
  }

  Map<String, dynamic> _asMap(Object? raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) return raw.map((key, value) => MapEntry('$key', value));
    return const {};
  }
}
