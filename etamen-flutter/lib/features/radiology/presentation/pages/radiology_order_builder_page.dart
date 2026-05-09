import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/app_text_field.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/radiology/data/models/create_radiology_order_request.dart';
import 'package:etamen_app/features/radiology/presentation/providers/radiology_providers.dart';
import 'package:etamen_app/features/radiology/presentation/widgets/radiology_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class RadiologyOrderBuilderPage extends ConsumerStatefulWidget {
  const RadiologyOrderBuilderPage({super.key});

  @override
  ConsumerState<RadiologyOrderBuilderPage> createState() =>
      _RadiologyOrderBuilderPageState();
}

class _RadiologyOrderBuilderPageState
    extends ConsumerState<RadiologyOrderBuilderPage> {
  final _notesController = TextEditingController();

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(radiologyCartControllerProvider);
    final cartController = ref.read(radiologyCartControllerProvider.notifier);
    final createState = ref.watch(createRadiologyOrderControllerProvider);

    return AppScaffold(
      title: uxCopy(context, 'مراجعة طلب الأشعة', 'Review radiology order'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (cart.isEmpty)
            EmptyView(
              message: uxCopy(
                context,
                'لم تختر أي فحوصات أشعة بعد',
                'No radiology scans selected yet',
              ),
              icon: Icons.biotech_outlined,
            )
          else ...[
            SoftMedicalCard(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    uxCopy(
                      context,
                      'الإجمالي التقديري من أسعار المركز',
                      'Estimated total from center prices',
                    ),
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    '${cart.localTotal.toStringAsFixed(2)} EGP',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      color: AppColors.medicalAccentDark,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    uxCopy(
                      context,
                      'هذا الرقم للعرض فقط. السيرفر يعيد حساب السعر ولا يقبل سعرًا من التطبيق.',
                      'This is display only. The backend recalculates the price and does not accept app-provided totals.',
                    ),
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppColors.muted,
                      height: 1.35,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            ...cart.items.map(
              (item) => RadiologyCartItemTile(
                item: item,
                onIncrement: () => cartController.updateQuantity(
                  item.scanId,
                  item.quantity + 1,
                ),
                onDecrement: () => cartController.updateQuantity(
                  item.scanId,
                  item.quantity - 1,
                ),
                onRemove: () => cartController.remove(item.scanId),
              ),
            ),
            const SizedBox(height: 12),
            AppTextField(
              controller: _notesController,
              label: uxCopy(context, 'ملاحظات للمركز', 'Notes for center'),
              maxLines: 3,
            ),
            if (createState.error != null) ...[
              const SizedBox(height: 12),
              Text(
                createState.error!.message,
                style: const TextStyle(color: AppColors.danger),
              ),
            ],
            const SizedBox(height: 24),
            AppButton(
              label: uxCopy(context, 'إنشاء الطلب', 'Create order'),
              isLoading: createState.isSubmitting,
              onPressed: createState.isSubmitting ? null : _createOrder,
            ),
          ],
        ],
      ),
    );
  }

  Future<void> _createOrder() async {
    final cart = ref.read(radiologyCartControllerProvider);
    if (cart.isEmpty || cart.providerId == null) return;

    final order = await ref
        .read(createRadiologyOrderControllerProvider.notifier)
        .create(
          CreateRadiologyOrderRequest(
            providerId: cart.providerId!,
            branchId: cart.branchId,
            items: cart.items,
            patientNotes: _notesController.text.trim(),
          ),
        );

    if (order == null || !mounted) return;
    ref.read(radiologyCartControllerProvider.notifier).clear();
    context.go(RouteNames.radiologyOrderDetails(order.id));
  }
}
