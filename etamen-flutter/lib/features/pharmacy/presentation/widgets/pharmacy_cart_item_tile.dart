import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_cart_item.dart';
import 'package:flutter/material.dart';

class PharmacyCartItemTile extends StatelessWidget {
  const PharmacyCartItemTile({
    required this.item,
    required this.onIncrease,
    required this.onDecrease,
    required this.onRemove,
    super.key,
  });

  final PharmacyCartItem item;
  final VoidCallback onIncrease;
  final VoidCallback onDecrease;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: ListTile(
        leading: const Icon(Icons.medication_outlined),
        title: Text(item.product.name),
        subtitle: Text(
          '${item.product.price} ${item.product.currency} x ${item.quantity}',
        ),
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
              tooltip: l10n.get('addToCart'),
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
