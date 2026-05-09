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
      padding: EdgeInsets.zero,
      children: [
        _LegacyHomeHero(
          name: firstName,
          onSearchTap: () => context.push(RouteNames.doctors),
          onBookDoctorTap: () => context.push(RouteNames.doctors),
        ),
        Padding(
          padding: const EdgeInsets.fromLTRB(14, 12, 14, 26),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _OldFinderPromoBanner(
                onTap: () => context.push(RouteNames.doctors),
              ),
              const SizedBox(height: 12),
              _OldUpcomingAppointmentCard(onTap: () => onOpenTab(1)),
              const SizedBox(height: 16),
              HomeSectionHeader(
                title: uxCopy(context, 'التخصصات', 'Speciality'),
                subtitle: uxCopy(
                  context,
                  'اختر تخصصًا وابدأ الحجز مثل التطبيق القديم.',
                  'Choose a category and start like the old app.',
                ),
              ),
              const SizedBox(height: 10),
              _OldSpecialityStrip(
                onDoctorsTap: () => context.push(RouteNames.doctors),
                onPharmacyTap: () => context.push(RouteNames.pharmacies),
                onLabsTap: () => context.push(RouteNames.labs),
              ),
              const SizedBox(height: 18),
              HomeSectionHeader(
                title: uxCopy(context, 'أطباء قريبون', 'Nearby Doctors'),
                subtitle: uxCopy(
                  context,
                  'كروت مختصرة للحجز السريع.',
                  'Compact cards for quick booking.',
                ),
              ),
              const SizedBox(height: 10),
              _OldNearbyDoctorsPreview(
                onTap: () => context.push(RouteNames.doctors),
              ),
              const SizedBox(height: 22),
              HomeSectionHeader(
                title: uxCopy(context, 'خدمات أخرى', 'More services'),
                subtitle: uxCopy(
                  context,
                  'الصيدلية والمعمل والمتابعة تظهر بعد تجربة الحجز الأساسية.',
                  'Pharmacy, labs, and follow-up stay below the booking path.',
                ),
              ),
              const SizedBox(height: 10),
              LayoutBuilder(
                builder: (context, constraints) {
                  final narrow = constraints.maxWidth < 380;
                  return GridView.count(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    crossAxisCount: 2,
                    mainAxisSpacing: 10,
                    crossAxisSpacing: 10,
                    childAspectRatio: narrow ? 1.08 : 1.18,
                    children: [
                      FeatureActionCard(
                        icon: Icons.local_pharmacy_outlined,
                        title: uxCopy(context, 'صيدلية', 'Pharmacy'),
                        subtitle: uxCopy(
                          context,
                          'منتجات وروشتة',
                          'Orders and Rx',
                        ),
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
                        subtitle: uxCopy(context, 'قياسات اليوم', 'Track'),
                        onTap: () => context.push(RouteNames.addVital()),
                      ),
                      FeatureActionCard(
                        icon: Icons.smart_toy_outlined,
                        title: uxCopy(context, 'المساعد', 'AI'),
                        subtitle: uxCopy(context, 'تنظيم فقط', 'Safe help'),
                        onTap: () => context.push(RouteNames.ai),
                      ),
                    ],
                  );
                },
              ),
              const SizedBox(height: 18),
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
            ],
          ),
        ),
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
          icon: Icons.local_hospital_outlined,
          title: uxCopy(context, 'المستشفيات والمراكز', 'Hospitals'),
          body: uxCopy(
            context,
            'اختار مستشفى، راجع الأقسام، وافتح طبيب للحجز بنفس خطوات المواعيد والدفع.',
            'Choose a hospital, review departments, then book a doctor through the same payment flow.',
          ),
          actionLabel: uxCopy(context, 'المستشفيات', 'Hospitals'),
          onTap: () => context.push(RouteNames.hospitals),
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
        ServiceCard(
          icon: Icons.medical_information_outlined,
          title: uxCopy(context, 'الأشعة', 'Radiology'),
          body: uxCopy(
            context,
            'اختار فحص أشعة من مركز معتمد، ادفع يدويًا، وتابع النتيجة من التطبيق بدون تفسير طبي.',
            'Choose a scan from an approved center, pay manually, and follow results without medical interpretation.',
          ),
          actionLabel: uxCopy(context, 'الأشعة', 'Radiology'),
          onTap: () => context.push(RouteNames.radiology),
          secondaryLabel: uxCopy(context, 'طلبات الأشعة', 'Radiology orders'),
          onSecondaryTap: () => context.push(RouteNames.radiologyOrders),
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
        ? uxCopy(context, 'أهلًا بك', 'Welcome, User')
        : uxCopy(context, 'أهلًا، $name', 'Welcome, $name');

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(
        14,
        MediaQuery.paddingOf(context).top + 12,
        14,
        18,
      ),
      decoration: const BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(12)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Expanded(
                child: Text.rich(
                  TextSpan(
                    children: [
                      TextSpan(
                        text: greeting.contains(',')
                            ? '${greeting.split(',').first}, '
                            : '',
                        style: const TextStyle(fontWeight: FontWeight.w500),
                      ),
                      TextSpan(
                        text: greeting.contains(',')
                            ? greeting.split(',').skip(1).join(',').trim()
                            : greeting,
                        style: const TextStyle(fontWeight: FontWeight.w900),
                      ),
                    ],
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.titleLarge?.copyWith(
                    color: Colors.white,
                    fontSize: 24,
                    height: 1.1,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Center(
                  child: NotificationBadge(
                    onTap: () => context.push(RouteNames.notifications),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: Material(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(9),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(9),
                    onTap: onSearchTap,
                    child: SizedBox(
                      height: 48,
                      child: Row(
                        children: [
                          const SizedBox(width: 14),
                          Expanded(
                            child: Text(
                              uxCopy(
                                context,
                                'ابحث باسم الطبيب',
                                'Search doctor by name',
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: Theme.of(context).textTheme.bodySmall
                                  ?.copyWith(
                                    color: Colors.grey.shade400,
                                    fontWeight: FontWeight.w700,
                                  ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Material(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                child: InkWell(
                  borderRadius: BorderRadius.circular(12),
                  onTap: onBookDoctorTap,
                  child: const SizedBox(
                    width: 48,
                    height: 48,
                    child: Icon(Icons.search, color: AppColors.primary),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _OldFinderPromoBanner extends StatelessWidget {
  const _OldFinderPromoBanner({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.legacyPanel,
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: onTap,
        child: Container(
          height: 132,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            gradient: const LinearGradient(
              begin: AlignmentDirectional.centerStart,
              end: AlignmentDirectional.centerEnd,
              colors: [AppColors.primary, AppColors.legacyPanel],
            ),
          ),
          child: Stack(
            children: [
              PositionedDirectional(
                end: -20,
                top: -22,
                child: Container(
                  width: 124,
                  height: 160,
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.28),
                    borderRadius: BorderRadius.circular(60),
                  ),
                ),
              ),
              PositionedDirectional(
                end: 8,
                bottom: 0,
                child: Container(
                  width: 84,
                  height: 108,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: const Icon(
                    Icons.medical_services_outlined,
                    color: AppColors.primary,
                    size: 46,
                  ),
                ),
              ),
              PositionedDirectional(
                start: 2,
                top: 0,
                bottom: 0,
                end: 104,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Row(
                      children: [
                        const Icon(
                          Icons.add_box_outlined,
                          color: Colors.white,
                          size: 14,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          uxCopy(context, 'مركز طبي', 'MEDICAL CENTER'),
                          style: Theme.of(context).textTheme.labelSmall
                              ?.copyWith(
                                color: Colors.white,
                                fontSize: 8,
                                fontWeight: FontWeight.w900,
                              ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      uxCopy(
                        context,
                        'احجز مع أطباء قريبين\nحسب التخصص',
                        'Search Doctors\nNearby With Sorted\nby Speciality',
                      ),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        height: 1.05,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      uxCopy(
                        context,
                        'اختر من تخصصات مختلفة',
                        'Choose from different categories',
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.labelSmall?.copyWith(
                        color: Colors.white.withValues(alpha: 0.80),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _OldUpcomingAppointmentCard extends StatelessWidget {
  const _OldUpcomingAppointmentCard({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: onTap,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        children: [
          Text(
            uxCopy(
              context,
              'لا توجد مواعيد قادمة',
              "You don't have any appointment",
            ),
            textAlign: TextAlign.center,
            style: Theme.of(
              context,
            ).textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 5),
          Text.rich(
            TextSpan(
              text: uxCopy(
                context,
                'ابحث عن أفضل الأطباء حسب التخصص، ',
                'Find best doctors near you by speciality, ',
              ),
              children: [
                TextSpan(
                  text: uxCopy(context, 'ابدأ الآن', 'click here'),
                  style: const TextStyle(color: AppColors.medicalAccentDark),
                ),
              ],
            ),
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: AppColors.muted,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _OldSpecialityStrip extends StatelessWidget {
  const _OldSpecialityStrip({
    required this.onDoctorsTap,
    required this.onPharmacyTap,
    required this.onLabsTap,
  });

  final VoidCallback onDoctorsTap;
  final VoidCallback onPharmacyTap;
  final VoidCallback onLabsTap;

  @override
  Widget build(BuildContext context) {
    final items = [
      _OldSpecialityItem(
        icon: Icons.medical_services_outlined,
        label: uxCopy(context, 'أسنان', 'Dentist'),
        color: AppColors.medicalAccentDark,
        onTap: onDoctorsTap,
      ),
      _OldSpecialityItem(
        icon: Icons.favorite_border,
        label: uxCopy(context, 'قلب', 'Cardiologist'),
        color: const Color(0xFF8ADFE8),
        onTap: onDoctorsTap,
      ),
      _OldSpecialityItem(
        icon: Icons.local_pharmacy_outlined,
        label: uxCopy(context, 'صيدلية', 'Pharmacy'),
        color: const Color(0xFF22D8D0),
        onTap: onPharmacyTap,
      ),
      _OldSpecialityItem(
        icon: Icons.biotech_outlined,
        label: uxCopy(context, 'معمل', 'Laboratory'),
        color: const Color(0xFF38BDF8),
        onTap: onLabsTap,
      ),
    ];

    return SizedBox(
      height: 54,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) => items[index],
      ),
    );
  }
}

class _OldSpecialityItem extends StatelessWidget {
  const _OldSpecialityItem({
    required this.icon,
    required this.label,
    required this.color,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(9),
      child: InkWell(
        borderRadius: BorderRadius.circular(9),
        onTap: onTap,
        child: Container(
          width: 126,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(9),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 12,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: Row(
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.labelMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _OldNearbyDoctorsPreview extends StatelessWidget {
  const _OldNearbyDoctorsPreview({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _OldNearbyDoctorMiniCard(
            initials: 'دأ',
            name: uxCopy(context, 'د. أحمد التجريبي', 'Dr. Myles Abbott'),
            specialty: uxCopy(context, 'قلب وأوعية', 'Orthopedic'),
            onTap: onTap,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _OldNearbyDoctorMiniCard(
            initials: 'دت',
            name: uxCopy(context, 'د. طبيب تجريبي', 'Dr Mark Smith'),
            specialty: uxCopy(context, 'أطفال', 'Ayurveda'),
            onTap: onTap,
          ),
        ),
      ],
    );
  }
}

class _OldNearbyDoctorMiniCard extends StatelessWidget {
  const _OldNearbyDoctorMiniCard({
    required this.initials,
    required this.name,
    required this.specialty,
    required this.onTap,
  });

  final String initials;
  final String name;
  final String specialty;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: onTap,
      padding: const EdgeInsets.all(8),
      child: Column(
        children: [
          Container(
            height: 118,
            width: double.infinity,
            decoration: BoxDecoration(
              color: AppColors.medicalMint,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.softBorder),
            ),
            child: Center(
              child: DoctorFinderSilhouette(initials: initials, size: 74),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            name,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: Theme.of(
              context,
            ).textTheme.labelLarge?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 2),
          Text(
            specialty,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: AppColors.muted,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class DoctorFinderSilhouette extends StatelessWidget {
  const DoctorFinderSilhouette({
    required this.initials,
    this.size = 72,
    super.key,
  });

  final String initials;
  final double size;

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        Container(
          width: size,
          height: size,
          decoration: const BoxDecoration(
            color: Colors.white,
            shape: BoxShape.circle,
          ),
        ),
        Positioned(
          top: size * 0.17,
          child: Container(
            width: size * 0.36,
            height: size * 0.36,
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.14),
              shape: BoxShape.circle,
            ),
          ),
        ),
        Positioned(
          bottom: size * 0.20,
          child: Container(
            width: size * 0.62,
            height: size * 0.30,
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.14),
              borderRadius: BorderRadius.vertical(
                top: Radius.circular(size * 0.24),
                bottom: Radius.circular(size * 0.08),
              ),
            ),
          ),
        ),
        Text(
          initials,
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
            color: AppColors.primaryPressed,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
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
