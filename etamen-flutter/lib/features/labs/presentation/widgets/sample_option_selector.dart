import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:flutter/material.dart';

class SampleOptionSelector extends StatelessWidget {
  const SampleOptionSelector({
    required this.value,
    required this.onChanged,
    super.key,
  });

  final LabSampleCollectionMethod value;
  final ValueChanged<LabSampleCollectionMethod> onChanged;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return SegmentedButton<LabSampleCollectionMethod>(
      segments: [
        ButtonSegment(
          value: LabSampleCollectionMethod.branchVisit,
          icon: const Icon(Icons.storefront_outlined),
          label: Text(l10n.get('branchVisit')),
        ),
        ButtonSegment(
          value: LabSampleCollectionMethod.homeCollection,
          icon: const Icon(Icons.home_outlined),
          label: Text(l10n.get('homeCollection')),
        ),
      ],
      selected: {value},
      onSelectionChanged: (selection) => onChanged(selection.first),
    );
  }
}
