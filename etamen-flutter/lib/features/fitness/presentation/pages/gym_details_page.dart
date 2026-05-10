import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/app_text_field.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_models.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';
import 'package:etamen_app/features/fitness/presentation/providers/fitness_providers.dart';
import 'package:etamen_app/features/fitness/presentation/widgets/fitness_widgets.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class GymDetailsPage extends ConsumerStatefulWidget {
  const GymDetailsPage({required this.gymId, super.key});

  final int gymId;

  @override
  ConsumerState<GymDetailsPage> createState() => _GymDetailsPageState();
}

class _GymDetailsPageState extends ConsumerState<GymDetailsPage> {
  final _notesController = TextEditingController();

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(gymDetailsControllerProvider(widget.gymId));
    final controller = ref.read(
      gymDetailsControllerProvider(widget.gymId).notifier,
    );
    final createState = ref.watch(createGymBookingControllerProvider);

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل الجيم', 'Gym details'),
      actions: [
        if (state.gym != null &&
            (state.plans.isNotEmpty || state.classes.isNotEmpty))
          IconButton(
            tooltip: uxCopy(context, 'حجز سريع', 'Quick booking'),
            onPressed: createState.isSubmitting
                ? null
                : () {
                    if (state.plans.isNotEmpty) {
                      _bookPlan(state.plans.first);
                    } else {
                      _bookClass(state.classes.first);
                    }
                  },
            icon: const Icon(Icons.flash_on_outlined),
          ),
      ],
      floatingActionButton:
          state.gym != null &&
              (state.plans.isNotEmpty || state.classes.isNotEmpty)
          ? FloatingActionButton.extended(
              onPressed: createState.isSubmitting
                  ? null
                  : () {
                      if (state.plans.isNotEmpty) {
                        _bookPlan(state.plans.first);
                      } else {
                        _bookClass(state.classes.first);
                      }
                    },
              icon: const Icon(Icons.flash_on_outlined),
              label: Text(uxCopy(context, 'حجز سريع', 'Quick booking')),
            )
          : null,
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null && state.gym == null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.gym == null)
              EmptyView(
                message: uxCopy(context, 'الجيم غير متاح.', 'Gym unavailable.'),
                icon: Icons.fitness_center_outlined,
              )
            else ...[
              _GymHeader(gym: state.gym!),
              const SizedBox(height: 14),
              SoftMedicalCard(
                child: Text(
                  uxCopy(
                    context,
                    'اختار خطة أو حصة، والسعر النهائي يتم حسابه من النظام.',
                    'Choose a plan or class. The final price is calculated by the backend.',
                  ),
                  style: const TextStyle(color: AppColors.muted),
                ),
              ),
              if (state.plans.isNotEmpty || state.classes.isNotEmpty) ...[
                const SizedBox(height: 14),
                _QuickGymBookingCard(
                  firstPlan: state.plans.isNotEmpty ? state.plans.first : null,
                  firstClass: state.classes.isNotEmpty
                      ? state.classes.first
                      : null,
                  isLoading: createState.isSubmitting,
                  onBookPlan: state.plans.isNotEmpty
                      ? () => _bookPlan(state.plans.first)
                      : null,
                  onBookClass: state.classes.isNotEmpty
                      ? () => _bookClass(state.classes.first)
                      : null,
                ),
              ],
              const SizedBox(height: 14),
              AppTextField(
                controller: _notesController,
                label: uxCopy(context, 'ملاحظات اختيارية', 'Optional notes'),
                maxLines: 2,
              ),
              const SizedBox(height: 18),
              _SectionTitle(
                title: uxCopy(context, 'خطط العضوية', 'Membership plans'),
              ),
              const SizedBox(height: 8),
              if (state.plans.isEmpty)
                EmptyView(
                  message: uxCopy(
                    context,
                    'لا توجد خطط عضوية متاحة.',
                    'No membership plans available.',
                  ),
                  icon: Icons.card_membership_outlined,
                )
              else
                ...state.plans.map(
                  (plan) => _PlanCard(
                    plan: plan,
                    isLoading: createState.isSubmitting,
                    onBook: () => _bookPlan(plan),
                  ),
                ),
              const SizedBox(height: 18),
              _SectionTitle(title: uxCopy(context, 'الحصص', 'Classes')),
              const SizedBox(height: 8),
              if (state.classes.isEmpty)
                EmptyView(
                  message: uxCopy(
                    context,
                    'لا توجد حصص متاحة حاليًا.',
                    'No classes are available right now.',
                  ),
                  icon: Icons.event_available_outlined,
                )
              else
                ...state.classes.map(
                  (gymClass) => _ClassCard(
                    gymClass: gymClass,
                    isLoading: createState.isSubmitting,
                    onBook: () => _bookClass(gymClass),
                  ),
                ),
              if (createState.error != null) ...[
                const SizedBox(height: 12),
                Text(
                  createState.error!.message,
                  style: const TextStyle(color: AppColors.danger),
                ),
              ],
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _bookPlan(GymMembershipPlan plan) async {
    final booking = await ref
        .read(createGymBookingControllerProvider.notifier)
        .create(
          CreateGymBookingRequest(
            providerId: widget.gymId,
            membershipPlanId: plan.id,
            notes: _notesController.text,
          ),
        );
    if (!mounted || booking == null) return;
    _goAfterBooking(booking);
  }

  Future<void> _bookClass(GymClass gymClass) async {
    final booking = await ref
        .read(createGymBookingControllerProvider.notifier)
        .create(
          CreateGymBookingRequest(
            providerId: widget.gymId,
            gymClassId: gymClass.id,
            notes: _notesController.text,
          ),
        );
    if (!mounted || booking == null) return;
    _goAfterBooking(booking);
  }

  void _goAfterBooking(GymBooking booking) {
    if (booking.paymentId != null) {
      context.go(
        RouteNames.payment(booking.paymentId!, gymBookingId: booking.id),
      );
      return;
    }
    context.go(RouteNames.gymBookingDetails(booking.id));
  }
}

class _GymHeader extends StatelessWidget {
  const _GymHeader({required this.gym});

  final Gym gym;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final branch = gym.primaryBranch;
    return SoftMedicalCard(
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
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.fitness_center_outlined,
                  color: AppColors.primaryDark,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      gym.name(isArabic),
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    if (gym.locationLabel.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        gym.locationLabel,
                        style: const TextStyle(color: AppColors.muted),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          if (gym.description(isArabic) != null) ...[
            const SizedBox(height: 12),
            Text(gym.description(isArabic)!),
          ],
          if (branch != null && branch.address(isArabic).isNotEmpty) ...[
            const SizedBox(height: 12),
            FitnessInfoLine(
              label: uxCopy(context, 'العنوان', 'Address'),
              value: branch.address(isArabic),
            ),
          ],
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              if (gym.menAllowed)
                FitnessBadge(label: uxCopy(context, 'رجال', 'Men')),
              if (gym.womenAllowed)
                FitnessBadge(label: uxCopy(context, 'سيدات', 'Women')),
              if (gym.ladiesOnlyHours)
                FitnessBadge(
                  label: uxCopy(context, 'مواعيد للسيدات', 'Ladies hours'),
                ),
              if (gym.hasClasses)
                FitnessBadge(label: uxCopy(context, 'حصص جماعية', 'Classes')),
            ],
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    return Text(
      title,
      style: Theme.of(
        context,
      ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
    );
  }
}

class _QuickGymBookingCard extends StatelessWidget {
  const _QuickGymBookingCard({
    required this.firstPlan,
    required this.firstClass,
    required this.isLoading,
    required this.onBookPlan,
    required this.onBookClass,
  });

  final GymMembershipPlan? firstPlan;
  final GymClass? firstClass;
  final bool isLoading;
  final VoidCallback? onBookPlan;
  final VoidCallback? onBookClass;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            uxCopy(context, 'اختيار سريع', 'Quick booking'),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          Text(
            uxCopy(
              context,
              'ابدأ بأول خطة أو حصة متاحة، والسعر يتأكد من الباك إند.',
              'Start with the first available plan or class. The backend confirms the price.',
            ),
            style: const TextStyle(color: AppColors.muted),
          ),
          if (firstPlan != null) ...[
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: AppButton(
                label: uxCopy(
                  context,
                  'احجز ${firstPlan!.name(isArabic)} - ${fitnessMoney(firstPlan!.price)}',
                  'Book ${firstPlan!.name(isArabic)} - ${fitnessMoney(firstPlan!.price)}',
                ),
                isLoading: isLoading,
                onPressed: onBookPlan,
              ),
            ),
          ],
          if (firstClass != null) ...[
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: isLoading ? null : onBookClass,
                icon: const Icon(Icons.event_available_outlined, size: 18),
                label: Text(
                  uxCopy(
                    context,
                    'احجز حصة ${firstClass!.name(isArabic)} - ${fitnessMoney(firstClass!.price)}',
                    'Book ${firstClass!.name(isArabic)} - ${fitnessMoney(firstClass!.price)}',
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({
    required this.plan,
    required this.onBook,
    required this.isLoading,
  });

  final GymMembershipPlan plan;
  final VoidCallback onBook;
  final bool isLoading;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            plan.name(isArabic),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          if (plan.description(isArabic) != null) ...[
            const SizedBox(height: 6),
            Text(
              plan.description(isArabic)!,
              style: const TextStyle(color: AppColors.muted),
            ),
          ],
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FitnessBadge(
                label: uxCopy(
                  context,
                  '${plan.durationDays} يوم',
                  '${plan.durationDays} days',
                ),
              ),
              if (plan.sessionsCount != null)
                FitnessBadge(
                  label: uxCopy(
                    context,
                    '${plan.sessionsCount} حصة',
                    '${plan.sessionsCount} sessions',
                  ),
                ),
              if (plan.includesClasses)
                FitnessBadge(label: uxCopy(context, 'تشمل حصص', 'Classes')),
              if (plan.includesPersonalTraining)
                FitnessBadge(
                  label: uxCopy(context, 'تدريب شخصي', 'Personal training'),
                ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Text(
                  fitnessMoney(plan.price),
                  style: Theme.of(
                    context,
                  ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
                ),
              ),
              AppButton(
                label: uxCopy(context, 'احجز', 'Book'),
                isLoading: isLoading,
                onPressed: isLoading ? null : onBook,
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ClassCard extends StatelessWidget {
  const _ClassCard({
    required this.gymClass,
    required this.onBook,
    required this.isLoading,
  });

  final GymClass gymClass;
  final VoidCallback onBook;
  final bool isLoading;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            gymClass.name(isArabic),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 10),
          FitnessInfoLine(
            label: uxCopy(context, 'المعاد', 'Time'),
            value: dateTimeLabel(gymClass.startsAt),
          ),
          if (gymClass.capacity != null)
            FitnessInfoLine(
              label: uxCopy(context, 'السعة', 'Capacity'),
              value: gymClass.capacity.toString(),
            ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Text(
                  fitnessMoney(gymClass.price),
                  style: Theme.of(
                    context,
                  ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
                ),
              ),
              AppButton(
                label: uxCopy(context, 'احجز', 'Book'),
                isLoading: isLoading,
                onPressed: isLoading ? null : onBook,
              ),
            ],
          ),
        ],
      ),
    );
  }
}
