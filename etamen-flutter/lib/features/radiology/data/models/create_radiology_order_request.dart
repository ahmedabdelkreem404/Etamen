import 'package:etamen_app/features/radiology/domain/entities/radiology_cart_item.dart';

class CreateRadiologyOrderRequest {
  const CreateRadiologyOrderRequest({
    required this.providerId,
    required this.items,
    this.branchId,
    this.patientNotes,
    this.scheduledAt,
  });

  final int providerId;
  final int? branchId;
  final List<RadiologyCartItem> items;
  final String? patientNotes;
  final DateTime? scheduledAt;

  Map<String, dynamic> toJson() {
    return {
      'provider_id': providerId,
      if (branchId != null) 'branch_id': branchId,
      if (patientNotes?.trim().isNotEmpty == true)
        'patient_notes': patientNotes!.trim(),
      if (scheduledAt != null) 'scheduled_at': scheduledAt!.toIso8601String(),
      'scans': items
          .map(
            (item) => {
              'radiology_scan_id': item.scanId,
              'quantity': item.quantity,
            },
          )
          .toList(growable: false),
    };
  }
}
