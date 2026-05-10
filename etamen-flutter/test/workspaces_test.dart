import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('workspace response parses patient provider and admin workspaces', () {
    final response = WorkspacesResponse.fromJson({
      'user': {'id': 1, 'name': 'Pilot', 'email': 'pilot@example.test'},
      'default_workspace': 'patient',
      'available_workspaces': [
        {
          'type': 'patient',
          'key': 'patient',
          'label_ar': 'حسابي كمريض',
          'label_en': 'My patient account',
          'permissions': [],
        },
        {
          'type': 'provider',
          'key': 'provider:12',
          'provider_id': 12,
          'provider_type': 'hospital',
          'provider_name_ar': 'مستشفى اطمن',
          'provider_name_en': 'Etamen Hospital',
          'role': 'owner',
          'is_owner': true,
          'permissions': ['manage_staff', 'manage_departments'],
          'status': 'active',
        },
        {
          'type': 'platform_admin',
          'key': 'platform_admin',
          'label_ar': 'إدارة المنصة',
          'label_en': 'Platform Admin',
          'permissions': ['platform_admin'],
        },
      ],
    });

    expect(response.user.email, 'pilot@example.test');
    expect(response.availableWorkspaces, hasLength(3));
    expect(response.workspaceByKey('provider:12')?.providerId, 12);
    expect(response.workspaceByKey('provider:12')?.typeLabel(true), 'مستشفى');
    expect(response.workspaceByKey('platform_admin')?.isPlatformAdmin, true);
  });

  test('provider dashboard parses summary cards and quick actions safely', () {
    final dashboard = ProviderDashboard.fromJson({
      'provider': {
        'id': 12,
        'type': 'hospital',
        'name_ar': 'مستشفى اطمن',
        'name_en': 'Etamen Hospital',
        'status': 'approved',
        'is_active': true,
      },
      'role': 'owner',
      'is_owner': true,
      'permissions': ['manage_staff', 'view_bookings'],
      'today_count': 2,
      'pending_payment_review_count': 1,
      'pending_actions_count': 3,
      'summary_cards': [
        {
          'key': 'departments',
          'label_ar': 'الأقسام',
          'label_en': 'Departments',
          'value': 5,
        },
      ],
      'quick_actions': [
        {
          'key': 'departments',
          'label_ar': 'الأقسام',
          'label_en': 'Departments',
        },
      ],
    });

    expect(dashboard.provider.name(true), 'مستشفى اطمن');
    expect(dashboard.permissions, contains('manage_staff'));
    expect(dashboard.summaryCards.single.value, 5);
    expect(dashboard.quickActions.single.label(true), 'الأقسام');
  });

  test('workspace endpoints and routes are stable', () {
    expect(ApiEndpoints.workspaces, '/me/workspaces');
    expect(
      ApiEndpoints.providerWorkspaceDashboard(12),
      '/provider/workspace/12/dashboard',
    );
    expect(RouteNames.providerDashboard(12), '/workspace/provider/12');
    expect(RouteNames.platformAdminDashboard, '/workspace/platform-admin');
  });
}
