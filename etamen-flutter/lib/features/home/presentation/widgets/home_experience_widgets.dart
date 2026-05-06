import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_badge.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

String uxCopy(BuildContext context, String ar, String en) {
  return AppLocalizations.of(context).isArabic ? ar : en;
}

class MainShellTopBar extends ConsumerWidget implements PreferredSizeWidget {
  const MainShellTopBar({required this.title, super.key});

  final String title;

  @override
  Size get preferredSize => const Size.fromHeight(78);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return SafeArea(
      bottom: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 10, 16, 8),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.text,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    uxCopy(
                      context,
                      'كل خدماتك الصحية في مكان واحد',
                      'Your care, organized in one place',
                    ),
                    style: Theme.of(
                      context,
                    ).textTheme.bodySmall?.copyWith(color: AppColors.muted),
                  ),
                ],
              ),
            ),
            SizedBox(
              width: 52,
              height: 52,
              child: Center(
                child: NotificationBadge(
                  onTap: () => context.push(RouteNames.notifications),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class HomeDashboardTab extends ConsumerWidget {
  const HomeDashboardTab({required this.onOpenTab, super.key});

  final ValueChanged<int> onOpenTab;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authControllerProvider);
    final name = authState.user?.name?.trim();
    final firstName = name == null || name.isEmpty
        ? null
        : name.split(' ').first;

    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
      children: [
        _GreetingCard(name: firstName),
        const SizedBox(height: 18),
        _SoftSearchCard(onTap: () => onOpenTab(2)),
        const SizedBox(height: 18),
        HomeSectionHeader(
          title: uxCopy(context, 'ابدأ بسرعة', 'Quick actions'),
          subtitle: uxCopy(
            context,
            'اختار الخدمة اللي محتاجها من غير لف كتير',
            'Jump into the care flow you need',
          ),
        ),
        const SizedBox(height: 10),
        LayoutBuilder(
          builder: (context, constraints) {
            final narrow = constraints.maxWidth < 380;
            return GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: narrow ? 2 : 4,
              mainAxisSpacing: 10,
              crossAxisSpacing: 10,
              childAspectRatio: narrow ? 1.02 : 0.86,
              children: [
                FeatureActionCard(
                  icon: Icons.medical_services_outlined,
                  title: uxCopy(context, 'احجز دكتور', 'Book doctor'),
                  subtitle: uxCopy(
                    context,
                    'اختار التخصص والموعد',
                    'Find a slot',
                  ),
                  onTap: () => context.push(RouteNames.doctors),
                ),
                FeatureActionCard(
                  icon: Icons.local_pharmacy_outlined,
                  title: uxCopy(context, 'اطلب دواء', 'Pharmacy'),
                  subtitle: uxCopy(
                    context,
                    'صيدليات ومنتجات',
                    'Products and orders',
                  ),
                  onTap: () => context.push(RouteNames.pharmacies),
                ),
                FeatureActionCard(
                  icon: Icons.biotech_outlined,
                  title: uxCopy(context, 'احجز تحليل', 'Book lab'),
                  subtitle: uxCopy(
                    context,
                    'تحاليل وباقات',
                    'Tests and packages',
                  ),
                  onTap: () => context.push(RouteNames.labs),
                ),
                FeatureActionCard(
                  icon: Icons.monitor_heart_outlined,
                  title: uxCopy(context, 'أضف قياس', 'Add vital'),
                  subtitle: uxCopy(context, 'ضغط، سكر، وزن', 'Vitals tracking'),
                  onTap: () => context.push(RouteNames.addVital()),
                ),
              ],
            );
          },
        ),
        const SizedBox(height: 22),
        HomeSectionHeader(
          title: uxCopy(context, 'نظرة اليوم', 'Today overview'),
          subtitle: uxCopy(
            context,
            'اختصارات تساعدك تتابع اللي يهمك',
            'Shortcuts for the things that matter',
          ),
        ),
        const SizedBox(height: 10),
        PatientSummaryCard(
          icon: Icons.event_available_outlined,
          title: uxCopy(context, 'مواعيدك القادمة', 'Upcoming appointments'),
          body: uxCopy(
            context,
            'تابع مواعيدك وحالة الدفع من هنا.',
            'Review appointments and payment state.',
          ),
          actionLabel: uxCopy(context, 'عرض المواعيد', 'View appointments'),
          onTap: () => onOpenTab(1),
        ),
        PatientSummaryCard(
          icon: Icons.medication_outlined,
          title: uxCopy(context, 'جرعات اليوم', 'Today medications'),
          body: uxCopy(
            context,
            'سجّل الجرعات كمأخوذة أو متخطاة للتنظيم فقط.',
            'Mark taken or skipped for organization only.',
          ),
          actionLabel: uxCopy(context, 'جرعات اليوم', 'Today doses'),
          onTap: () => context.push(RouteNames.todayMedications),
        ),
        PatientSummaryCard(
          icon: Icons.health_and_safety_outlined,
          title: uxCopy(context, 'متابعة صحتك', 'Health follow-up'),
          body: uxCopy(
            context,
            'قياسات، أدوية، وخطط متابعة في مساحة واحدة.',
            'Vitals, reminders, and care plans together.',
          ),
          actionLabel: uxCopy(context, 'فتح المتابعة', 'Open health'),
          onTap: () => onOpenTab(3),
        ),
        const SizedBox(height: 16),
        _AiSafeCta(),
      ],
    );
  }
}

class ServicesTab extends StatelessWidget {
  const ServicesTab({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
      children: [
        HomeSectionHeader(
          title: uxCopy(context, 'الخدمات', 'Services'),
          subtitle: uxCopy(
            context,
            'احجز دكتور، اطلب من صيدلية، أو احجز تحليل.',
            'Doctors, pharmacy, and labs in one place.',
          ),
        ),
        const SizedBox(height: 12),
        ServiceCard(
          icon: Icons.medical_services_outlined,
          title: uxCopy(context, 'احجز دكتور', 'Book a doctor'),
          body: uxCopy(
            context,
            'ابحث عن دكتور، شوف التفاصيل، واختار موعد مناسب.',
            'Search doctors, review profiles, and choose a slot.',
          ),
          actionLabel: uxCopy(context, 'الأطباء', 'Doctors'),
          onTap: () => context.push(RouteNames.doctors),
        ),
        ServiceCard(
          icon: Icons.local_pharmacy_outlined,
          title: uxCopy(context, 'اطلب من صيدلية', 'Pharmacy orders'),
          body: uxCopy(
            context,
            'اختار صيدلية ومنتجات، وارفع الروشتة لو مطلوبة.',
            'Choose products and upload a prescription when needed.',
          ),
          actionLabel: uxCopy(context, 'الصيدليات', 'Pharmacies'),
          onTap: () => context.push(RouteNames.pharmacies),
          secondaryLabel: uxCopy(context, 'طلباتي', 'My orders'),
          onSecondaryTap: () => context.push(RouteNames.pharmacyOrders),
        ),
        ServiceCard(
          icon: Icons.biotech_outlined,
          title: uxCopy(context, 'احجز تحليل', 'Lab orders'),
          body: uxCopy(
            context,
            'اختار معمل وتحاليل، وحدد زيارة الفرع أو سحب عينة.',
            'Choose lab tests and sample collection method.',
          ),
          actionLabel: uxCopy(context, 'المعامل', 'Labs'),
          onTap: () => context.push(RouteNames.labs),
          secondaryLabel: uxCopy(context, 'طلبات المعمل', 'Lab orders'),
          onSecondaryTap: () => context.push(RouteNames.labOrders),
        ),
      ],
    );
  }
}

class HealthHubTab extends StatelessWidget {
  const HealthHubTab({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
      children: [
        HomeSectionHeader(
          title: uxCopy(context, 'المتابعة الصحية', 'Health follow-up'),
          subtitle: uxCopy(
            context,
            'تنظيم وقياسات وتذكيرات، بدون تشخيص أو نصيحة علاجية.',
            'Tracking and organization, not diagnosis or treatment.',
          ),
        ),
        const SizedBox(height: 12),
        ServiceCard(
          icon: Icons.monitor_heart_outlined,
          title: uxCopy(context, 'قياساتك', 'Vitals'),
          body: uxCopy(
            context,
            'سجّل الضغط، السكر، الوزن، وباقي القياسات للمتابعة.',
            'Track blood pressure, sugar, weight, and more.',
          ),
          actionLabel: uxCopy(context, 'فتح القياسات', 'Open vitals'),
          onTap: () => context.push(RouteNames.health),
        ),
        ServiceCard(
          icon: Icons.medication_liquid_outlined,
          title: uxCopy(context, 'الأدوية', 'Medications'),
          body: uxCopy(
            context,
            'تذكيرات وتنظيم فقط، وليست وصفة أو تعديل جرعة.',
            'Reminders for organization only, never a prescription.',
          ),
          actionLabel: uxCopy(context, 'تذكيرات الأدوية', 'Reminders'),
          onTap: () => context.push(RouteNames.medications),
        ),
        ServiceCard(
          icon: Icons.assignment_outlined,
          title: uxCopy(context, 'خطط المتابعة', 'Care plans'),
          body: uxCopy(
            context,
            'تابع الخطة، سجّل وجباتك، وشوف ملخص الالتزام.',
            'Review plans, meal logs, and commitment summaries.',
          ),
          actionLabel: uxCopy(context, 'خطط المتابعة', 'Care plans'),
          onTap: () => context.push(RouteNames.carePlans),
        ),
        ServiceCard(
          icon: Icons.smart_toy_outlined,
          title: uxCopy(context, 'المساعد الذكي', 'AI assistant'),
          body: uxCopy(
            context,
            'يساعدك تنظم المعلومات، وليس بديلاً عن الطبيب.',
            'Helps organize information; not a doctor.',
          ),
          actionLabel: uxCopy(context, 'فتح المساعد', 'Open assistant'),
          onTap: () => context.push(RouteNames.ai),
        ),
      ],
    );
  }
}

class HomeSectionHeader extends StatelessWidget {
  const HomeSectionHeader({required this.title, this.subtitle, super.key});

  final String title;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: Theme.of(context).textTheme.titleLarge?.copyWith(
            fontWeight: FontWeight.w800,
            color: AppColors.text,
          ),
        ),
        if (subtitle != null) ...[
          const SizedBox(height: 4),
          Text(
            subtitle!,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: AppColors.muted,
              height: 1.35,
            ),
          ),
        ],
      ],
    );
  }
}

class FeatureActionCard extends StatelessWidget {
  const FeatureActionCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
    super.key,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: onTap,
      padding: const EdgeInsets.all(10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _IconBadge(icon: icon, size: 36),
          const SizedBox(height: 10),
          Text(
            title,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(
              context,
            ).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: AppColors.muted,
              height: 1.2,
            ),
          ),
        ],
      ),
    );
  }
}

class ServiceCard extends StatelessWidget {
  const ServiceCard({
    required this.icon,
    required this.title,
    required this.body,
    required this.actionLabel,
    required this.onTap,
    this.secondaryLabel,
    this.onSecondaryTap,
    super.key,
  });

  final IconData icon;
  final String title;
  final String body;
  final String actionLabel;
  final VoidCallback onTap;
  final String? secondaryLabel;
  final VoidCallback? onSecondaryTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _IconBadge(icon: icon, size: 46),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  body,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.muted,
                    height: 1.35,
                  ),
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    FilledButton.icon(
                      onPressed: onTap,
                      icon: const Icon(Icons.arrow_forward, size: 16),
                      label: Text(actionLabel),
                    ),
                    if (secondaryLabel != null && onSecondaryTap != null)
                      OutlinedButton(
                        onPressed: onSecondaryTap,
                        child: Text(secondaryLabel!),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class PatientSummaryCard extends StatelessWidget {
  const PatientSummaryCard({
    required this.icon,
    required this.title,
    required this.body,
    required this.actionLabel,
    required this.onTap,
    super.key,
  });

  final IconData icon;
  final String title;
  final String body;
  final String actionLabel;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 10),
      onTap: onTap,
      child: Row(
        children: [
          _IconBadge(icon: icon, size: 42),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(
                    context,
                  ).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 4),
                Text(
                  body,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.muted,
                    height: 1.3,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          StatusPill(label: actionLabel),
        ],
      ),
    );
  }
}

class StatusPill extends StatelessWidget {
  const StatusPill({required this.label, super.key});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: 0.09),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
          color: AppColors.primary,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class SoftMedicalCard extends StatelessWidget {
  const SoftMedicalCard({
    required this.child,
    this.onTap,
    this.padding = const EdgeInsets.all(16),
    this.margin,
    super.key,
  });

  final Widget child;
  final VoidCallback? onTap;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry? margin;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: margin,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.035),
            blurRadius: 16,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: onTap,
          child: Padding(padding: padding, child: child),
        ),
      ),
    );
  }
}

class _GreetingCard extends StatelessWidget {
  const _GreetingCard({required this.name});

  final String? name;

  @override
  Widget build(BuildContext context) {
    final greeting = name == null
        ? uxCopy(context, 'أهلاً بيك', 'Hello')
        : uxCopy(context, 'أهلاً يا $name', 'Hello, $name');

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  greeting,
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  uxCopy(
                    context,
                    'تابع مواعيدك وصحتك من مكان واحد.',
                    'Track appointments and health in one calm place.',
                  ),
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Colors.white.withValues(alpha: 0.88),
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 14),
          Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              Icons.health_and_safety_outlined,
              color: Colors.white,
              size: 32,
            ),
          ),
        ],
      ),
    );
  }
}

class _SoftSearchCard extends StatelessWidget {
  const _SoftSearchCard({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: onTap,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
      child: Row(
        children: [
          const Icon(Icons.search, color: AppColors.primary),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              uxCopy(
                context,
                'ابحث عن دكتور أو خدمة',
                'Search for a doctor or service',
              ),
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: AppColors.muted,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          const Icon(Icons.tune_outlined, color: AppColors.muted, size: 20),
        ],
      ),
    );
  }
}

class _AiSafeCta extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: () => context.push(RouteNames.ai),
      child: Row(
        children: [
          _IconBadge(icon: Icons.smart_toy_outlined, size: 46),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(
                    context,
                    'اسأل المساعد الذكي بأمان',
                    'Ask the AI assistant safely',
                  ),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  uxCopy(
                    context,
                    'يساعدك في تنظيم المعلومات، وليس بديلاً عن الطبيب.',
                    'It helps organize information and is not a doctor.',
                  ),
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.muted,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
          const Icon(Icons.chevron_right, color: AppColors.muted),
        ],
      ),
    );
  }
}

class _IconBadge extends StatelessWidget {
  const _IconBadge({required this.icon, this.size = 40});

  final IconData icon;
  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Icon(icon, color: AppColors.primary, size: size * 0.48),
    );
  }
}
