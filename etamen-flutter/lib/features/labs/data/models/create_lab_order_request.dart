import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';

enum LabSampleCollectionMethod {
  branchVisit('branch_visit'),
  homeCollection('home_collection');

  const LabSampleCollectionMethod(this.wireValue);

  final String wireValue;
}

class CreateLabOrderRequest {
  const CreateLabOrderRequest({
    required this.labProviderId,
    required this.items,
    required this.sampleCollectionMethod,
    this.collectionAddress,
    this.notes,
  });

  final int labProviderId;
  final List<LabCartItem> items;
  final LabSampleCollectionMethod sampleCollectionMethod;
  final String? collectionAddress;
  final String? notes;

  Map<String, dynamic> toJson() {
    return {
      'lab_provider_id': labProviderId,
      'sample_collection_method': sampleCollectionMethod.wireValue,
      if (collectionAddress?.trim().isNotEmpty == true)
        'collection_address': collectionAddress!.trim(),
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
      'items': items
          .map(
            (item) => {
              'item_type': item.type == LabCartItemType.test
                  ? 'test'
                  : 'package',
              if (item.type == LabCartItemType.test) 'test_id': item.itemId,
              if (item.type == LabCartItemType.package)
                'package_id': item.itemId,
              'quantity': item.quantity,
            },
          )
          .toList(growable: false),
    };
  }
}
