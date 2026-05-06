import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';
import 'package:flutter/material.dart';

class LabCartItemTile extends StatelessWidget {
  const LabCartItemTile({
    required this.item,
    required this.onIncrease,
    required this.onDecrease,
    required this.onRemove,
    super.key,
  });

  final LabCartItem item;
  final VoidCallback onIncrease;
  final VoidCallback onDecrease;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: ListTile(
        leading: Icon(
          item.type == LabCartItemType.test
              ? Icons.science_outlined
              : Icons.inventory_2_outlined,
        ),
        title: Text(item.name),
        subtitle: Text('${item.price} ${item.currency} x ${item.quantity}'),
        trailing: Wrap(
          crossAxisAlignment: WrapCrossAlignment.center,
          children: [
            IconButton(
              tooltip: l10n.get('remove'),
              onPressed: onDecrease,
              icon: const Icon(Icons.remove_circle_outline),
            ),
            Text('${item.quantity}'),
            IconButton(
              tooltip: l10n.get('addToLabOrder'),
              onPressed: onIncrease,
              icon: const Icon(Icons.add_circle_outline),
            ),
            IconButton(
              tooltip: l10n.get('removeFile'),
              onPressed: onRemove,
              icon: const Icon(Icons.delete_outline),
            ),
          ],
        ),
      ),
    );
  }
}
