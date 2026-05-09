import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/presentation/widgets/payment_status_badge.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_cart_item.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan_category.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

class RadiologyHeroCard extends StatelessWidget {
  const RadiologyHeroCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.primaryDark, AppColors.primary, AppColors.cyan],
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: AppColors.primaryDark.withValues(alpha: 0.16),
            blurRadius: 24,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.16),
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              Icons.medical_information_outlined,
              color: Colors.white,
              size: 30,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(context, 'الأشعة', 'Radiology'),
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  uxCopy(
                    context,
                    'احجز فحوصات الأشعة من مراكز معتمدة وتابع نتيجة الطلب من التطبيق.',
                    'Book radiology scans from approved centers and follow results in the app.',
                  ),
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: Colors.white.withValues(alpha: 0.88),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class RadiologyCategoryChip extends StatelessWidget {
  const RadiologyCategoryChip({
    required this.category,
    required this.selected,
    required this.onTap,
    super.key,
  });

  final RadiologyScanCategory category;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Padding(
      padding: const EdgeInsetsDirectional.only(end: 8),
      child: ChoiceChip(
        selected: selected,
        label: Text(category.name(l10n.isArabic)),
        avatar: selected
            ? const Icon(Icons.check_circle, size: 18)
            : const Icon(Icons.radio_button_unchecked, size: 18),
        onSelected: (_) => onTap(),
      ),
    );
  }
}

class RadiologyScanCard extends StatelessWidget {
  const RadiologyScanCard({
    required this.scan,
    required this.isSelected,
    required this.onAdd,
    super.key,
  });

  final RadiologyScan scan;
  final bool isSelected;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final provider = scan.provider;
    final branch = scan.branch;
    final location = branch?.address(l10n.isArabic);
    final prep = scan.preparation(l10n.isArabic);

    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  color: AppColors.medicalMint,
                  borderRadius: BorderRadius.circular(17),
                  border: Border.all(color: AppColors.softBorder),
                ),
                child: const Icon(
                  Icons.biotech_outlined,
                  color: AppColors.primary,
                  size: 30,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      scan.name(l10n.isArabic),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      provider?.name(l10n.isArabic) ??
                          uxCopy(context, 'مركز أشعة معتمد', 'Approved center'),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: AppColors.muted,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Text(
                scan.priceLabel,
                style: Theme.of(context).textTheme.labelLarge?.copyWith(
                  color: AppColors.medicalAccentDark,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
          if (location?.isNotEmpty == true) ...[
            const SizedBox(height: 10),
            _IconLine(icon: Icons.location_on_outlined, value: location!),
          ],
          const SizedBox(height: 10),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              if (scan.category != null)
                _MiniBadge(label: scan.category!.name(l10n.isArabic)),
              if (scan.durationMinutes != null)
                _MiniBadge(
                  label: uxCopy(
                    context,
                    '${scan.durationMinutes} دقيقة',
                    '${scan.durationMinutes} min',
                  ),
                ),
              if (scan.requiresPreparation)
                _MiniBadge(
                  label: uxCopy(context, 'تحضير مطلوب', 'Preparation'),
                ),
              if (scan.requiresFasting)
                _MiniBadge(label: uxCopy(context, 'صيام', 'Fasting')),
              if (scan.contrastRequired)
                _MiniBadge(label: uxCopy(context, 'صبغة', 'Contrast')),
              if (scan.homeAvailable)
                _MiniBadge(label: uxCopy(context, 'منزل', 'Home')),
              if (scan.branchAvailable)
                _MiniBadge(label: uxCopy(context, 'داخل المركز', 'Branch')),
            ],
          ),
          if (prep?.isNotEmpty == true) ...[
            const SizedBox(height: 10),
            Text(
              prep!,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                color: AppColors.softText,
                height: 1.35,
              ),
            ),
          ],
          const SizedBox(height: 12),
          Align(
            alignment: AlignmentDirectional.centerEnd,
            child: FilledButton.icon(
              onPressed: onAdd,
              icon: Icon(isSelected ? Icons.playlist_add_check : Icons.add),
              label: Text(
                isSelected
                    ? uxCopy(context, 'مضاف للطلب', 'Selected')
                    : uxCopy(context, 'إضافة للطلب', 'Add'),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class RadiologyCartSummary extends StatelessWidget {
  const RadiologyCartSummary({
    required this.itemCount,
    required this.total,
    required this.onOpen,
    super.key,
  });

  final int itemCount;
  final double total;
  final VoidCallback onOpen;

  @override
  Widget build(BuildContext context) {
    if (itemCount == 0) return const SizedBox.shrink();

    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          const Icon(Icons.playlist_add_check_circle, color: AppColors.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              uxCopy(
                context,
                '$itemCount فحص محدد - ${total.toStringAsFixed(2)} جنيه',
                '$itemCount scan(s) - ${total.toStringAsFixed(2)} EGP',
              ),
              style: const TextStyle(fontWeight: FontWeight.w800),
            ),
          ),
          TextButton(
            onPressed: onOpen,
            child: Text(uxCopy(context, 'مراجعة', 'Review')),
          ),
        ],
      ),
    );
  }
}

class RadiologyCartItemTile extends StatelessWidget {
  const RadiologyCartItemTile({
    required this.item,
    required this.onIncrement,
    required this.onDecrement,
    required this.onRemove,
    super.key,
  });

  final RadiologyCartItem item;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: ListTile(
        leading: const Icon(Icons.biotech_outlined, color: AppColors.primary),
        title: Text(item.scan.name(l10n.isArabic)),
        subtitle: Text('${item.scan.priceLabel} x ${item.quantity}'),
        trailing: Wrap(
          crossAxisAlignment: WrapCrossAlignment.center,
          children: [
            IconButton(
              tooltip: uxCopy(context, 'تقليل', 'Decrease'),
              onPressed: onDecrement,
              icon: const Icon(Icons.remove_circle_outline),
            ),
            Text(
              item.quantity.toString(),
              style: const TextStyle(fontWeight: FontWeight.w900),
            ),
            IconButton(
              tooltip: uxCopy(context, 'زيادة', 'Increase'),
              onPressed: onIncrement,
              icon: const Icon(Icons.add_circle_outline),
            ),
            IconButton(
              tooltip: uxCopy(context, 'حذف', 'Remove'),
              onPressed: onRemove,
              icon: const Icon(Icons.delete_outline, color: AppColors.danger),
            ),
          ],
        ),
      ),
    );
  }
}

class RadiologyStatusChip extends StatelessWidget {
  const RadiologyStatusChip({required this.status, super.key});

  final RadiologyOrderStatus status;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final color = switch (status) {
      RadiologyOrderStatus.paid ||
      RadiologyOrderStatus.accepted ||
      RadiologyOrderStatus.completed ||
      RadiologyOrderStatus.resultReady => AppColors.success,
      RadiologyOrderStatus.rejected ||
      RadiologyOrderStatus.cancelledByPatient ||
      RadiologyOrderStatus.cancelledByProvider => AppColors.danger,
      RadiologyOrderStatus.pendingPayment ||
      RadiologyOrderStatus.pendingPaymentReview => AppColors.primary,
      _ => AppColors.muted,
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        status.friendlyLabel(l10n.isArabic),
        style: TextStyle(color: color, fontWeight: FontWeight.w800),
      ),
    );
  }
}

class RadiologyPaymentCard extends StatelessWidget {
  const RadiologyPaymentCard({
    required this.order,
    required this.paymentStatus,
    super.key,
  });

  final RadiologyOrder order;
  final PaymentStatusDetails? paymentStatus;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            uxCopy(context, 'الدفع', 'Payment'),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: Text(
                  '${order.totalAmount ?? '-'} ${order.currency}',
                  style: Theme.of(
                    context,
                  ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
                ),
              ),
              if (paymentStatus != null)
                PaymentStatusBadge(status: paymentStatus!.status)
              else
                RadiologyStatusChip(status: order.status),
            ],
          ),
          const SizedBox(height: 12),
          if (paymentStatus?.status == PaymentStatusEnum.verified ||
              order.status == RadiologyOrderStatus.paid ||
              order.status == RadiologyOrderStatus.accepted ||
              order.status == RadiologyOrderStatus.inProgress ||
              order.status == RadiologyOrderStatus.resultReady ||
              order.status == RadiologyOrderStatus.completed)
            Text(
              uxCopy(
                context,
                'تم تأكيد الدفع من الإدارة.',
                'Payment verified.',
              ),
              style: const TextStyle(
                color: AppColors.success,
                fontWeight: FontWeight.w800,
              ),
            )
          else if (order.paymentId != null)
            AppButton(
              label: uxCopy(context, 'اختيار طريقة الدفع', 'Choose payment'),
              onPressed: () => context.push(
                RouteNames.payment(
                  order.paymentId!,
                  radiologyOrderId: order.id,
                ),
              ),
            )
          else
            Text(
              uxCopy(
                context,
                'لا يوجد طلب دفع متاح لهذا الطلب.',
                'No payment request is available for this order.',
              ),
              style: const TextStyle(color: AppColors.muted),
            ),
        ],
      ),
    );
  }
}

class RadiologyResultCard extends StatelessWidget {
  const RadiologyResultCard({
    required this.result,
    required this.isDownloading,
    required this.onDownload,
    super.key,
  });

  final RadiologyResult result;
  final bool isDownloading;
  final Future<void> Function() onDownload;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final notes = result.notes(l10n.isArabic);
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.description_outlined, color: AppColors.primary),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  result.title(l10n.isArabic),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
          if (result.uploadedAt != null) ...[
            const SizedBox(height: 8),
            Text(
              DateFormat(
                'd MMM yyyy, h:mm a',
              ).format(result.uploadedAt!.toLocal()),
              style: const TextStyle(color: AppColors.muted),
            ),
          ],
          if (notes?.isNotEmpty == true) ...[
            const SizedBox(height: 8),
            Text(notes!),
          ],
          const SizedBox(height: 8),
          Text(
            uxCopy(
              context,
              'يرجى مراجعة الطبيب أو مركز الأشعة لفهم النتيجة.',
              'Please review the result with your doctor or radiology center.',
            ),
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: AppColors.softText,
              height: 1.35,
            ),
          ),
          const SizedBox(height: 12),
          AppButton(
            label: uxCopy(context, 'تحميل النتيجة', 'Download result'),
            isLoading: isDownloading,
            onPressed: isDownloading ? null : onDownload,
          ),
        ],
      ),
    );
  }
}

class _MiniBadge extends StatelessWidget {
  const _MiniBadge({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.softBorder),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
          color: AppColors.medicalAccentDark,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _IconLine extends StatelessWidget {
  const _IconLine({required this.icon, required this.value});

  final IconData icon;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, color: AppColors.muted, size: 18),
        const SizedBox(width: 6),
        Expanded(
          child: Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(
              context,
            ).textTheme.bodySmall?.copyWith(color: AppColors.muted),
          ),
        ),
      ],
    );
  }
}
