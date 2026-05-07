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
  Size get preferredSize => const Size.fromHeight(100);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return SafeArea(
      bottom: false,
      child: Container(
        height: preferredSize.height,
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 14),
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: AlignmentDirectional.topStart,
            end: AlignmentDirectional.bottomEnd,
            colors: [
              AppColors.primaryDark,
              AppColors.primary,
              AppColors.legacyTeal,
            ],
          ),
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(26)),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.w900,
                      color: Colors.white,
                      height: 1.05,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    uxCopy(
                      context,
                      'كل خدماتك الصحية في مكان واحد',
                      'Your care, organized in one place',
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: Colors.white.withValues(alpha: 0.86),
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            Container(
              width: 54,
              height: 54,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.primaryDark.withValues(alpha: 0.20),
                    blurRadius: 18,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
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
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 26),
      children: [
        _LegacyHomeHero(
          name: firstName,
          onSearchTap: () => context.push(RouteNames.doctors),
          onBookDoctorTap: () => context.push(RouteNames.doctors),
        ),
        const SizedBox(height: 14),
        _DoctorBookingHighlightCard(
          onTap: () => context.push(RouteNames.doctors),
        ),
        const SizedBox(height: 22),
        HomeSectionHeader(
          title: uxCopy(
            context,
            'التخصصات والخدمات',
            'Specialties and services',
          ),
          subtitle: uxCopy(
            context,
            'ابدأ بالحجز الطبي، ثم الصيدلية والمعمل والمتابعة.',
            'Start with doctor booking, then pharmacy, labs, and follow-up.',
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
              childAspectRatio: narrow ? 1.06 : 0.88,
              children: [
                FeatureActionCard(
                  icon: Icons.medical_services_outlined,
                  title: uxCopy(context, 'دكتور', 'Doctor'),
                  subtitle: uxCopy(context, 'احجز موعد', 'Book a slot'),
                  onTap: () => context.push(RouteNames.doctors),
                ),
                FeatureActionCard(
                  icon: Icons.local_pharmacy_outlined,
                  title: uxCopy(context, 'صيدلية', 'Pharmacy'),
                  subtitle: uxCopy(context, 'منتجات وروشتة', 'Orders and Rx'),
                  onTap: () => context.push(RouteNames.pharmacies),
                ),
                FeatureActionCard(
                  icon: Icons.biotech_outlined,
                  title: uxCopy(context, 'معمل', 'Labs'),
                  subtitle: uxCopy(context, 'تحاليل وباقات', 'Tests'),
                  onTap: () => context.push(RouteNames.labs),
                ),
                FeatureActionCard(
                  icon: Icons.monitor_heart_outlined,
                  title: uxCopy(context, 'متابعة', 'Vitals'),
                  subtitle: uxCopy(context, 'قياسات اليوم', 'Track today'),
                  onTap: () => context.push(RouteNames.addVital()),
                ),
              ],
            );
          },
        ),
        const SizedBox(height: 22),
        HomeSectionHeader(
          title: uxCopy(context, 'متابعة اليوم', 'Today follow-up'),
          subtitle: uxCopy(
            context,
            'مواعيدك وقياساتك وتنبيهاتك في كروت هادئة وواضحة.',
            'Appointments, vitals, and reminders in clear cards.',
          ),
        ),
        const SizedBox(height: 10),
        PatientSummaryCard(
          icon: Icons.event_available_outlined,
          title: uxCopy(context, 'المواعيد القادمة', 'Upcoming appointments'),
          body: uxCopy(
            context,
            'راجع موعدك القادم وحالة الدفع بدون تفاصيل معقدة.',
            'Review your next visit and payment state without clutter.',
          ),
          actionLabel: uxCopy(context, 'عرض', 'View'),
          onTap: () => onOpenTab(1),
        ),
        PatientSummaryCard(
          icon: Icons.medication_outlined,
          title: uxCopy(context, 'جرعات اليوم', 'Today medications'),
          body: uxCopy(
            context,
            'تنظيم الجرعات للتذكير فقط، وليس وصفًا أو تعديلًا للعلاج.',
            'Dose tracking for reminders only, never treatment advice.',
          ),
          actionLabel: uxCopy(context, 'جرعاتي', 'Doses'),
          onTap: () => context.push(RouteNames.todayMedications),
        ),
        PatientSummaryCard(
          icon: Icons.health_and_safety_outlined,
          title: uxCopy(context, 'متابعة صحتك', 'Health follow-up'),
          body: uxCopy(
            context,
            'قياسات وأدوية وخطط متابعة في مكان واحد.',
            'Vitals, reminders, and care plans together.',
          ),
          actionLabel: uxCopy(context, 'فتح', 'Open'),
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
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 26),
      children: [
        _TabIntroCard(
          icon: Icons.medical_services_outlined,
          title: uxCopy(context, 'الخدمات الطبية', 'Medical services'),
          body: uxCopy(
            context,
            'نفس بساطة Doctor Finder: اختر الخدمة ثم أكمل الخطوات الواضحة.',
            'Doctor Finder simplicity: choose a service, then follow clear steps.',
          ),
        ),
        const SizedBox(height: 14),
        ServiceCard(
          icon: Icons.person_search_outlined,
          title: uxCopy(context, 'احجز دكتور', 'Book a doctor'),
          body: uxCopy(
            context,
            'ابحث باسم الطبيب أو التخصص، راجع البروفايل، واحجز موعدك.',
            'Search by doctor or specialty, review the profile, and book.',
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
            'اختار المعمل والتحاليل وطريقة سحب العينة المناسبة.',
            'Choose lab tests and the right sample collection method.',
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
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 26),
      children: [
        _TabIntroCard(
          icon: Icons.health_and_safety_outlined,
          title: uxCopy(context, 'المتابعة الصحية', 'Health follow-up'),
          body: uxCopy(
            context,
            'تنظيم وقياسات وتذكيرات، بدون تشخيص أو نصيحة علاجية.',
            'Tracking and reminders, not diagnosis or treatment advice.',
          ),
        ),
        const SizedBox(height: 14),
        ServiceCard(
          icon: Icons.monitor_heart_outlined,
          title: uxCopy(context, 'قياساتك', 'Vitals'),
          body: uxCopy(
            context,
            'سجل الضغط والسكر والوزن وباقي القياسات للمتابعة.',
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
          actionLabel: uxCopy(context, 'التذكيرات', 'Reminders'),
          onTap: () => context.push(RouteNames.medications),
        ),
        ServiceCard(
          icon: Icons.assignment_outlined,
          title: uxCopy(context, 'خطط المتابعة', 'Care plans'),
          body: uxCopy(
            context,
            'تابع الخطة وسجل الوجبات وشوف ملخص الالتزام.',
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
            'يساعدك في تنظيم المعلومات، وليس بديلًا عن الطبيب.',
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
        Row(
          children: [
            Container(
              width: 4,
              height: 20,
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                title,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w900,
                  color: AppColors.text,
                ),
              ),
            ),
          ],
        ),
        if (subtitle != null) ...[
          const SizedBox(height: 5),
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
          _IconBadge(icon: icon, size: 40),
          const Spacer(),
          Text(
            title,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(
              context,
            ).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w900),
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
          _IconBadge(icon: icon, size: 48),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
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
                  ).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w900),
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
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.border),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
          color: AppColors.primaryDark,
          fontWeight: FontWeight.w900,
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
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.softBorder),
        boxShadow: [
          BoxShadow(
            color: AppColors.primaryDark.withValues(alpha: 0.08),
            blurRadius: 22,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: onTap,
          child: Padding(padding: padding, child: child),
        ),
      ),
    );
  }
}

class _LegacyHomeHero extends StatelessWidget {
  const _LegacyHomeHero({
    required this.name,
    required this.onSearchTap,
    required this.onBookDoctorTap,
  });

  final String? name;
  final VoidCallback onSearchTap;
  final VoidCallback onBookDoctorTap;

  @override
  Widget build(BuildContext context) {
    final greeting = name == null
        ? uxCopy(context, 'أهلًا بك في اطمن', 'Welcome to Etamen')
        : uxCopy(context, 'أهلًا يا $name', 'Hello, $name');

    return Container(
      padding: const EdgeInsets.fromLTRB(18, 18, 18, 16),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.24),
            blurRadius: 22,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Stack(
        children: [
          PositionedDirectional(
            top: -28,
            end: -30,
            child: Transform.rotate(
              angle: -0.35,
              child: Container(
                width: 140,
                height: 170,
                color: Colors.white.withValues(alpha: 0.10),
              ),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          greeting,
                          style: Theme.of(context).textTheme.headlineSmall
                              ?.copyWith(
                                color: Colors.white,
                                fontWeight: FontWeight.w900,
                                height: 1.08,
                              ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          uxCopy(
                            context,
                            'ابحث عن طبيب واحجز موعدك بخطوات بسيطة.',
                            'Find a doctor and book in simple steps.',
                          ),
                          style: Theme.of(context).textTheme.bodyMedium
                              ?.copyWith(
                                color: Colors.white.withValues(alpha: 0.90),
                                height: 1.4,
                              ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  const _HeroDoctorBadge(),
                ],
              ),
              const SizedBox(height: 18),
              Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(999),
                elevation: 0,
                child: InkWell(
                  borderRadius: BorderRadius.circular(999),
                  onTap: onSearchTap,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 8,
                    ),
                    child: Row(
                      children: [
                        const SizedBox(width: 8),
                        const Icon(Icons.search, color: AppColors.primary),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            uxCopy(
                              context,
                              'ابحث باسم الدكتور أو التخصص',
                              'Search doctor name or specialty',
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: Theme.of(context).textTheme.bodyMedium
                                ?.copyWith(
                                  color: AppColors.softText,
                                  fontWeight: FontWeight.w700,
                                ),
                          ),
                        ),
                        Container(
                          width: 42,
                          height: 42,
                          decoration: const BoxDecoration(
                            color: AppColors.appointmentOrange,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.arrow_forward,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _HeroShortcut(
                    icon: Icons.event_available_outlined,
                    label: uxCopy(context, 'احجز الآن', 'Book now'),
                    onTap: onBookDoctorTap,
                  ),
                  _HeroShortcut(
                    icon: Icons.medical_services_outlined,
                    label: uxCopy(context, 'التخصصات', 'Specialties'),
                    onTap: onBookDoctorTap,
                  ),
                  _HeroShortcut(
                    icon: Icons.notifications_none_outlined,
                    label: uxCopy(context, 'تنبيهاتك', 'Alerts'),
                    onTap: () => context.push(RouteNames.notifications),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _HeroDoctorBadge extends StatelessWidget {
  const _HeroDoctorBadge();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 76,
      height: 88,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: AppColors.primaryDark.withValues(alpha: 0.18),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          Positioned(
            top: 10,
            child: Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: AppColors.medicalMint,
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(
                Icons.person_outline,
                color: AppColors.primary,
                size: 30,
              ),
            ),
          ),
          Positioned(
            bottom: 12,
            child: Container(
              width: 52,
              height: 8,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(999),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _DoctorBookingHighlightCard extends StatelessWidget {
  const _DoctorBookingHighlightCard({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          Container(
            width: 74,
            height: 74,
            decoration: BoxDecoration(
              color: AppColors.medicalMint,
              borderRadius: BorderRadius.circular(22),
            ),
            child: const Icon(
              Icons.person_search_outlined,
              color: AppColors.primary,
              size: 36,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(context, 'حجز الأطباء أولًا', 'Doctor booking first'),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  uxCopy(
                    context,
                    'اختر التخصص والطبيب ثم الموعد المتاح.',
                    'Choose specialty, doctor, then the available slot.',
                  ),
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.muted,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          FilledButton(
            onPressed: onTap,
            child: Text(uxCopy(context, 'ابدأ', 'Start')),
          ),
        ],
      ),
    );
  }
}

class _HeroShortcut extends StatelessWidget {
  const _HeroShortcut({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white.withValues(alpha: 0.16),
      borderRadius: BorderRadius.circular(999),
      child: InkWell(
        borderRadius: BorderRadius.circular(999),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(icon, color: Colors.white, size: 16),
              const SizedBox(width: 6),
              Text(
                label,
                style: Theme.of(context).textTheme.labelMedium?.copyWith(
                  color: Colors.white,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _TabIntroCard extends StatelessWidget {
  const _TabIntroCard({
    required this.icon,
    required this.title,
    required this.body,
  });

  final IconData icon;
  final String title;
  final String body;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Row(
        children: [
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Icon(icon, color: Colors.white, size: 28),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  body,
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
                    'اسأل المساعد بأمان',
                    'Ask the assistant safely',
                  ),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  uxCopy(
                    context,
                    'ينظم المعلومات ولا يستبدل الطبيب أو الطوارئ.',
                    'Organizes information; it does not replace doctors or emergency care.',
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
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(size * 0.32),
        border: Border.all(color: AppColors.border),
      ),
      child: Icon(icon, color: AppColors.primary, size: size * 0.48),
    );
  }
}
