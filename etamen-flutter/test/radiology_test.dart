import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/payments/data/models/payment_status_model.dart';
import 'package:etamen_app/features/radiology/data/models/create_radiology_order_request.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_order_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_result_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_scan_category_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_scan_model.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_cart_item.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';
import 'package:etamen_app/features/radiology/presentation/providers/radiology_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('Radiology category and scan models parse safe public fields', () {
    final category = RadiologyScanCategoryModel.fromJson({
      'id': 1,
      'code': 'mri',
      'name_ar': 'رنين مغناطيسي',
      'name_en': 'MRI',
      'is_active': true,
    });
    final scan = RadiologyScanModel.fromJson({
      'id': 10,
      'provider_id': 7,
      'branch_id': 3,
      'radiology_scan_category_id': 1,
      'name_ar': 'رنين على المخ',
      'name_en': 'Brain MRI',
      'base_price': '1200.00',
      'duration_minutes': 30,
      'requires_preparation': true,
      'category': {'id': 1, 'code': 'mri', 'name_ar': 'رنين مغناطيسي'},
      'provider': {
        'id': 7,
        'name_ar': 'مركز اطمن للأشعة',
        'primary_area_name': 'مدينة نصر',
        'primary_city_name': 'القاهرة',
      },
      'branch': {'id': 3, 'provider_id': 7, 'address_ar': 'شارع تجريبي'},
    });

    expect(category.code, 'mri');
    expect(category.name(true), 'رنين مغناطيسي');
    expect(scan.id, 10);
    expect(scan.category?.code, 'mri');
    expect(scan.provider?.locationLabel, 'مدينة نصر - القاهرة');
    expect(scan.branch?.address(true), contains('شارع تجريبي'));
    expect(scan.priceValue, 1200);
  });

  test('CreateRadiologyOrderRequest never sends trusted totals or status', () {
    const scan = RadiologyScan(
      id: 10,
      providerId: 7,
      branchId: 3,
      categoryId: 1,
      nameAr: 'رنين على المخ',
      basePrice: '1200.00',
    );
    final request = CreateRadiologyOrderRequest(
      providerId: 7,
      branchId: 3,
      patientNotes: 'Test note',
      items: const [RadiologyCartItem(scan: scan, quantity: 2)],
    );

    final json = request.toJson();

    expect(json['provider_id'], 7);
    expect(json['branch_id'], 3);
    expect(json['patient_notes'], 'Test note');
    expect(json['scans'], [
      {'radiology_scan_id': 10, 'quantity': 2},
    ]);
    expect(json.containsKey('subtotal'), false);
    expect(json.containsKey('total_amount'), false);
    expect(json.containsKey('status'), false);
    expect(json.containsKey('payment_id'), false);
  });

  test('RadiologyOrderModel parses payment, results and friendly status', () {
    final order = RadiologyOrderModel.fromJson({
      'id': 33,
      'order_number': 'RAD-33',
      'status': 'result_ready',
      'total_amount': '1200.00',
      'payment_id': 501,
      'provider': {'id': 7, 'name_ar': 'مركز اطمن للأشعة'},
      'items': [
        {
          'id': 1,
          'radiology_scan_id': 10,
          'scan_name_ar': 'رنين على المخ',
          'unit_price': '1200.00',
          'quantity': 1,
          'total_price': '1200.00',
          'preparation_snapshot_ar': 'تعليمات عامة',
        },
      ],
      'results': [
        {
          'id': 8,
          'radiology_order_id': 33,
          'result_type': 'report_pdf',
          'title_ar': 'تقرير الأشعة',
          'is_visible_to_patient': true,
          'file': {'original_name': 'result.pdf'},
        },
      ],
    });

    expect(order.status, RadiologyOrderStatus.resultReady);
    expect(order.status.friendlyLabel(true), 'النتيجة جاهزة');
    expect(order.paymentId, 501);
    expect(order.items.single.preparation(true), 'تعليمات عامة');
    expect(order.results.single.fileName, 'result.pdf');
  });

  test('Radiology result model parses visible metadata without raw paths', () {
    final result = RadiologyResultModel.fromJson({
      'id': 8,
      'radiology_order_id': 33,
      'result_type': 'report_pdf',
      'title_ar': 'تقرير الأشعة',
      'notes_ar': 'راجع الطبيب لفهم النتيجة',
      'is_visible_to_patient': true,
      'download_url': '/api/v1/radiology/results/8/download',
      'file': {'original_name': 'result.pdf', 'mime_type': 'application/pdf'},
    });

    expect(result.title(true), 'تقرير الأشعة');
    expect(result.notes(true), 'راجع الطبيب لفهم النتيجة');
    expect(result.downloadUrl, '/api/v1/radiology/results/8/download');
    expect(result.fileName, 'result.pdf');
  });

  test('Radiology cart blocks mixing centers in one order', () {
    final controller = RadiologyCartController();
    const first = RadiologyScan(
      id: 1,
      providerId: 7,
      categoryId: 1,
      nameAr: 'رنين',
      basePrice: '1000',
    );
    const second = RadiologyScan(
      id: 2,
      providerId: 8,
      categoryId: 1,
      nameAr: 'مقطعية',
      basePrice: '800',
    );

    expect(controller.addScan(first), true);
    expect(controller.addScan(second), false);
    expect(controller.state.itemCount, 1);
    expect(controller.state.lastMessage, isNotNull);
  });

  test('Radiology endpoints and routes are stable', () {
    expect(ApiEndpoints.radiologyScanCategories, '/radiology/scan-categories');
    expect(ApiEndpoints.radiologyScans, '/radiology/scans');
    expect(ApiEndpoints.radiologyOrders, '/radiology/orders');
    expect(ApiEndpoints.radiologyOrder(33), '/radiology/orders/33');
    expect(
      ApiEndpoints.radiologyOrderResults(33),
      '/radiology/orders/33/results',
    );
    expect(
      ApiEndpoints.radiologyResultDownload(8),
      '/radiology/results/8/download',
    );
    expect(RouteNames.radiology, '/radiology');
    expect(RouteNames.radiologyOrderBuilder, '/radiology/order-builder');
    expect(RouteNames.radiologyOrders, '/radiology/orders');
    expect(RouteNames.radiologyOrderDetails(33), '/radiology/orders/33');
    expect(
      RouteNames.payment(501, radiologyOrderId: 33),
      '/payments/501?radiologyOrderId=33',
    );
  });

  test('Payment status model maps radiology order status safely', () {
    final status = PaymentStatusModel.fromJson({
      'id': 501,
      'status': 'verified',
      'amount': '1200.00',
      'currency': 'EGP',
      'payable': {'type': 'RadiologyOrder', 'id': 33},
      'radiology_order': {'id': 33, 'order_number': 'RAD-33', 'status': 'paid'},
    });

    expect(status.payableId, 33);
    expect(status.radiologyOrderStatus, 'paid');
  });
}
