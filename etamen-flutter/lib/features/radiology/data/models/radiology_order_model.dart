import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_order_item_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_provider_summary_model.dart';
import 'package:etamen_app/features/radiology/data/models/radiology_result_model.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';

class RadiologyOrderModel extends RadiologyOrder {
  const RadiologyOrderModel({
    required super.id,
    required super.status,
    required super.items,
    super.orderNumber,
    super.providerId,
    super.provider,
    super.branchId,
    super.branch,
    super.subtotal,
    super.discountAmount,
    super.totalAmount,
    super.currency,
    super.paymentId,
    super.patientNotes,
    super.scheduledAt,
    super.createdAt,
    super.results,
  });

  factory RadiologyOrderModel.fromJson(Map<String, dynamic> json) {
    final provider = asRadiologyMap(json['provider']);
    final branch = asRadiologyMap(json['branch']);
    final payment = asRadiologyMap(json['payment']);
    final items = radiologyList(
      json['items'],
    ).map(RadiologyOrderItemModel.fromJson).toList(growable: false);
    final results = radiologyList(json['results'])
        .map(RadiologyResultModel.fromJson)
        .where((result) => result.isVisibleToPatient)
        .toList(growable: false);

    return RadiologyOrderModel(
      id: radiologyInt(json['id']) ?? 0,
      orderNumber: json['order_number']?.toString(),
      providerId: radiologyInt(json['provider_id'] ?? provider?['id']),
      provider: provider == null
          ? null
          : RadiologyProviderSummaryModel.fromJson(provider),
      branchId: radiologyInt(json['branch_id'] ?? branch?['id']),
      branch: branch == null
          ? null
          : RadiologyBranchSummaryModel.fromJson(branch),
      status: RadiologyOrderStatus.fromWire(json['status']?.toString()),
      subtotal: json['subtotal']?.toString(),
      discountAmount: json['discount_amount']?.toString(),
      totalAmount: (json['total_amount'] ?? json['grand_total'])?.toString(),
      currency: (json['currency'] ?? payment?['currency'] ?? 'EGP').toString(),
      paymentId: radiologyInt(json['payment_id'] ?? payment?['id']),
      patientNotes: json['patient_notes']?.toString(),
      scheduledAt: DateTime.tryParse((json['scheduled_at'] ?? '').toString()),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()),
      items: items,
      results: results,
    );
  }
}
