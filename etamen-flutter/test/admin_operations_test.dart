import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/admin_operations/data/admin_operation_models.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('admin dashboard model parses counts and quick actions', () {
    final dashboard = AdminDashboard.fromJson({
      'pending_payment_reviews_count': 3,
      'pending_provider_approvals_count': 2,
      'open_support_tickets_count': 4,
      'open_refund_requests_count': 1,
      'unresolved_disputes_count': 5,
      'today_appointments_count': 6,
      'today_radiology_orders_count': 7,
      'today_gym_bookings_count': 8,
      'today_coach_bookings_count': 9,
      'quick_actions': [
        {
          'key': 'payment_reviews',
          'label_ar': 'مراجعة المدفوعات',
          'label_en': 'Payment reviews',
        },
      ],
      'recent_events': [
        {'id': 1, 'event': 'payment.accepted', 'created_at': 'now'},
      ],
    });

    expect(dashboard.counts['pending_payment_reviews_count'], 3);
    expect(dashboard.quickActions.single.key, 'payment_reviews');
    expect(dashboard.quickActions.single.label(true), 'مراجعة المدفوعات');
    expect(dashboard.recentEvents.single.title(false), 'payment.accepted');
  });

  test('admin list model unwraps paginated API responses', () {
    final response = AdminListResponse.fromJson({
      'data': [
        {
          'id': 12,
          'ticket_number': 'SUP-12',
          'status': 'pending_admin',
          'category': 'payment',
        },
      ],
    });

    expect(response.items.single.id, 12);
    expect(response.items.single.title(true), 'SUP-12');
    expect(response.items.single.subtitle(true), contains('في انتظار الإدارة'));
  });

  test('admin and support endpoints are stable', () {
    expect(
      ApiEndpoints.adminOperationsDashboard,
      '/admin/operations/dashboard',
    );
    expect(
      ApiEndpoints.adminOperationsList('payments/pending'),
      '/admin/operations/payments/pending',
    );
    expect(
      ApiEndpoints.adminOperationsAction('refunds', 4, 'approve'),
      '/admin/operations/refunds/4/approve',
    );
    expect(ApiEndpoints.supportTickets, '/support/tickets');
    expect(ApiEndpoints.refunds, '/refunds');
    expect(ApiEndpoints.disputes, '/disputes');
  });

  test('admin and user operation routes are stable', () {
    expect(
      RouteNames.adminPayments,
      '/workspace/platform-admin/payment-reviews',
    );
    expect(
      RouteNames.adminOperationDetails('providers', 9),
      '/workspace/platform-admin/providers/9',
    );
    expect(RouteNames.createSupportTicket, '/support/create');
    expect(RouteNames.refunds, '/support/refunds');
    expect(RouteNames.disputes, '/support/disputes');
  });

  test(
    'friendly admin status labels avoid raw backend status as primary label',
    () {
      expect(friendlyAdminStatus('pending_review', true), 'جاري المراجعة');
      expect(friendlyAdminStatus('approved', true), 'مقبول');
      expect(friendlyAdminStatus('waiting_provider', true), 'في انتظار المزود');
    },
  );
}
