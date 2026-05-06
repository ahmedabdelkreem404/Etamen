import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/presentation/providers/labs_providers.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_package_card.dart';
import 'package:etamen_app/features/labs/presentation/widgets/lab_test_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LabTestsPage extends ConsumerWidget {
  const LabTestsPage({required this.labId, super.key});

  final int labId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(labTestsControllerProvider(labId));
    final controller = ref.read(labTestsControllerProvider(labId).notifier);
    final cart = ref.watch(labCartControllerProvider);

    return DefaultTabController(
      length: 2,
      child: AppScaffold(
        title: l10n.get('tests'),
        floatingActionButton: cart.itemCount == 0
            ? null
            : FloatingActionButton.extended(
                onPressed: () => context.push(RouteNames.labCart),
                icon: const Icon(Icons.science_outlined),
                label: Text('${l10n.get('labOrderCart')} (${cart.itemCount})'),
              ),
        body: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: TextField(
                onChanged: controller.search,
                decoration: InputDecoration(
                  hintText: l10n.get('searchLabTest'),
                  prefixIcon: const Icon(Icons.search),
                ),
              ),
            ),
            TabBar(
              tabs: [
                Tab(text: l10n.get('tests')),
                Tab(text: l10n.get('packages')),
              ],
            ),
            Expanded(
              child: RefreshIndicator(
                onRefresh: controller.load,
                child: state.isLoading
                    ? const LoadingView()
                    : state.error != null
                    ? ErrorView(
                        message: state.error!.message,
                        onRetry: controller.load,
                      )
                    : TabBarView(
                        children: [
                          _TestsList(labId: labId, tests: state.filteredTests),
                          _PackagesList(
                            labId: labId,
                            packages: state.filteredPackages,
                          ),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TestsList extends ConsumerWidget {
  const _TestsList({required this.labId, required this.tests});

  final int labId;
  final List<LabTest> tests;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final cart = ref.watch(labCartControllerProvider);
    if (tests.isEmpty) {
      return EmptyView(message: l10n.get('noTests'), icon: Icons.science);
    }
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: tests
          .map(
            (test) => LabTestCard(
              test: test,
              quantity: _quantity(cart.items, LabCartItemType.test, test.id),
              onAdd: () => _addTest(context, ref, test),
              onIncrease: () => _addTest(context, ref, test),
              onDecrease: () => ref
                  .read(labCartControllerProvider.notifier)
                  .updateQuantity(
                    LabCartItemType.test,
                    test.id,
                    _quantity(cart.items, LabCartItemType.test, test.id) - 1,
                  ),
            ),
          )
          .toList(growable: false),
    );
  }

  int _quantity(List<LabCartItem> items, LabCartItemType type, int id) {
    for (final item in items) {
      if (item.type == type && item.itemId == id) return item.quantity;
    }
    return 0;
  }

  Future<void> _addTest(
    BuildContext context,
    WidgetRef ref,
    LabTest test,
  ) async {
    final controller = ref.read(labCartControllerProvider.notifier);
    final added = controller.addTest(test, labId: labId);
    if (added || !context.mounted) return;
    if (await _confirmClear(context)) {
      controller.addTest(test, labId: labId, clearExisting: true);
    }
  }
}

class _PackagesList extends ConsumerWidget {
  const _PackagesList({required this.labId, required this.packages});

  final int labId;
  final List<LabPackage> packages;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final cart = ref.watch(labCartControllerProvider);
    if (packages.isEmpty) {
      return EmptyView(message: l10n.get('noPackages'), icon: Icons.inventory);
    }
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: packages
          .map(
            (package) => LabPackageCard(
              package: package,
              quantity: _quantity(
                cart.items,
                LabCartItemType.package,
                package.id,
              ),
              onAdd: () => _addPackage(context, ref, package),
              onIncrease: () => _addPackage(context, ref, package),
              onDecrease: () => ref
                  .read(labCartControllerProvider.notifier)
                  .updateQuantity(
                    LabCartItemType.package,
                    package.id,
                    _quantity(cart.items, LabCartItemType.package, package.id) -
                        1,
                  ),
            ),
          )
          .toList(growable: false),
    );
  }

  int _quantity(List<LabCartItem> items, LabCartItemType type, int id) {
    for (final item in items) {
      if (item.type == type && item.itemId == id) return item.quantity;
    }
    return 0;
  }

  Future<void> _addPackage(
    BuildContext context,
    WidgetRef ref,
    LabPackage package,
  ) async {
    final controller = ref.read(labCartControllerProvider.notifier);
    final added = controller.addPackage(package, labId: labId);
    if (added || !context.mounted) return;
    if (await _confirmClear(context)) {
      controller.addPackage(package, labId: labId, clearExisting: true);
    }
  }
}

Future<bool> _confirmClear(BuildContext context) async {
  final l10n = AppLocalizations.of(context);
  final shouldClear = await showDialog<bool>(
    context: context,
    builder: (dialogContext) => AlertDialog(
      title: Text(l10n.get('clearCart')),
      content: Text(l10n.get('oneLabCartNotice')),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(dialogContext).pop(false),
          child: Text(l10n.get('back')),
        ),
        FilledButton(
          onPressed: () => Navigator.of(dialogContext).pop(true),
          child: Text(l10n.get('clearCart')),
        ),
      ],
    ),
  );
  return shouldClear == true;
}
