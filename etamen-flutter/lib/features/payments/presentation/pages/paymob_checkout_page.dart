import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

class PaymobCheckoutPage extends ConsumerWidget {
  const PaymobCheckoutPage({
    required this.paymentId,
    this.appointmentId,
    this.pharmacyOrderId,
    this.labOrderId,
    super.key,
  });

  final int paymentId;
  final int? appointmentId;
  final int? pharmacyOrderId;
  final int? labOrderId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(paymentControllerProvider(paymentId));
    final session = state.paymobSession;

    return AppScaffold(
      title: l10n.get('onlinePayment'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.credit_card, size: 36),
                  const SizedBox(height: 12),
                  Text(
                    l10n.get('paymobCheckoutTitle'),
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  const SizedBox(height: 8),
                  Text(l10n.get('paymobCheckoutSafety')),
                  const SizedBox(height: 12),
                  Text(
                    l10n.get('paymobRedirectNotProof'),
                    style: const TextStyle(color: AppColors.muted),
                  ),
                ],
              ),
            ),
          ),
          if (state.error != null) ...[
            const SizedBox(height: 12),
            Text(
              state.error!.message,
              style: const TextStyle(color: AppColors.danger),
            ),
          ],
          if (session != null && !session.hasCheckoutUrl) ...[
            const SizedBox(height: 12),
            Card(
              color: Colors.orange.shade50,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Text(l10n.get('paymobMissingCheckoutUrl')),
              ),
            ),
          ],
          const SizedBox(height: 24),
          AppButton(
            label: session?.hasCheckoutUrl == true
                ? l10n.get('openPaymentPage')
                : l10n.get('createPaymobSession'),
            isLoading: state.isCreatingPaymobSession,
            onPressed: state.isCreatingPaymobSession
                ? null
                : () => _createOrOpen(context, ref, session),
          ),
          const SizedBox(height: 12),
          AppButton(
            label: l10n.get('checkPaymentStatus'),
            onPressed: () => context.go(
              RouteNames.paymentStatus(
                paymentId,
                appointmentId: appointmentId,
                pharmacyOrderId: pharmacyOrderId,
                labOrderId: labOrderId,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _createOrOpen(
    BuildContext context,
    WidgetRef ref,
    Object? currentSession,
  ) async {
    final l10n = AppLocalizations.of(context);
    final controller = ref.read(paymentControllerProvider(paymentId).notifier);
    final session = currentSession == null
        ? await controller.createPaymobSession(paymentId)
        : ref.read(paymentControllerProvider(paymentId)).paymobSession;

    if (!context.mounted || session == null) return;
    final url = session.checkoutUrl;
    if (url == null || url.isEmpty) return;

    final launched = await launchUrl(
      Uri.parse(url),
      mode: LaunchMode.externalApplication,
    );
    if (!launched && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(l10n.get('cannotOpenPaymentPage'))),
      );
    }
  }
}
