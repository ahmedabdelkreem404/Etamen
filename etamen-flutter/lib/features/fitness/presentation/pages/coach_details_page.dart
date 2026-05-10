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

class CoachDetailsPage extends ConsumerStatefulWidget {
  const CoachDetailsPage({required this.coachId, super.key});

  final int coachId;

  @override
  ConsumerState<CoachDetailsPage> createState() => _CoachDetailsPageState();
}

class _CoachDetailsPageState extends ConsumerState<CoachDetailsPage> {
  final _goalController = TextEditingController();

  @override
  void dispose() {
    _goalController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(coachDetailsControllerProvider(widget.coachId));
    final controller = ref.read(
      coachDetailsControllerProvider(widget.coachId).notifier,
    );
    final createState = ref.watch(createCoachBookingControllerProvider);

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل الكوتش', 'Coach details'),
      actions: [
        if (state.coach != null && state.sessionTypes.isNotEmpty)
          IconButton(
            tooltip: uxCopy(context, 'حجز سريع', 'Quick booking'),
            onPressed: createState.isSubmitting
                ? null
                : () => _bookFirstSession(state),
            icon: const Icon(Icons.flash_on_outlined),
          ),
      ],
      floatingActionButton: state.coach != null && state.sessionTypes.isNotEmpty
          ? FloatingActionButton.extended(
              onPressed: createState.isSubmitting
                  ? null
                  : () => _bookFirstSession(state),
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
            else if (state.error != null && state.coach == null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.coach == null)
              EmptyView(
                message: uxCopy(
                  context,
                  'الكوتش غير متاح.',
                  'Coach unavailable.',
                ),
                icon: Icons.sports_handball_outlined,
              )
            else ...[
              _CoachHeader(coach: state.coach!),
              const SizedBox(height: 14),
              SoftMedicalCard(
                child: Text(
                  uxCopy(
                    context,
                    'اختر نوع الجلسة والموعد المتاح. المحتوى هنا تنظيم لياقة/تغذية وليس وصفة علاجية.',
                    'Choose a session and available slot. This is fitness or nutrition organization, not medical treatment.',
                  ),
                  style: const TextStyle(color: AppColors.muted),
                ),
              ),
              if (state.sessionTypes.isNotEmpty) ...[
                const SizedBox(height: 14),
                _QuickCoachBookingCard(
                  firstSession: state.sessionTypes.first,
                  firstSlot: state.availability.isNotEmpty
                      ? state.availability.first
                      : null,
                  isLoading: createState.isSubmitting,
                  onBook: createState.isSubmitting
                      ? null
                      : () => _bookFirstSession(state),
                ),
              ],
              const SizedBox(height: 14),
              AppTextField(
                controller: _goalController,
                label: uxCopy(context, 'هدفك من الجلسة', 'Session goal'),
                maxLines: 2,
              ),
              const SizedBox(height: 18),
              _SectionTitle(
                title: uxCopy(context, 'أنواع الجلسات', 'Sessions'),
              ),
              const SizedBox(height: 8),
              if (state.sessionTypes.isEmpty)
                EmptyView(
                  message: uxCopy(
                    context,
                    'لا توجد جلسات متاحة حاليًا.',
                    'No sessions are available right now.',
                  ),
                  icon: Icons.schedule_outlined,
                )
              else
                ...state.sessionTypes.map(
                  (session) => _SessionTypeCard(
                    session: session,
                    selected: state.selectedSessionType?.id == session.id,
                    onTap: () => controller.selectSessionType(session),
                  ),
                ),
              const SizedBox(height: 18),
              _SectionTitle(title: uxCopy(context, 'المواعيد', 'Availability')),
              const SizedBox(height: 8),
              if (state.availability.isEmpty)
                EmptyView(
                  message: uxCopy(
                    context,
                    'لا توجد مواعيد متاحة.',
                    'No available slots.',
                  ),
                  icon: Icons.event_busy_outlined,
                )
              else
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: state.availability
                      .map(
                        (slot) => ChoiceChip(
                          label: Text(dateTimeLabel(slot.startsAt)),
                          selected: state.selectedSlot?.id == slot.id,
                          onSelected: (_) => controller.selectSlot(slot),
                        ),
                      )
                      .toList(growable: false),
                ),
              const SizedBox(height: 18),
              _SectionTitle(title: uxCopy(context, 'الباقات', 'Packages')),
              const SizedBox(height: 8),
              if (state.packages.isEmpty)
                Text(
                  uxCopy(
                    context,
                    'لا توجد باقات متاحة حاليًا.',
                    'No packages are available right now.',
                  ),
                  style: const TextStyle(color: AppColors.muted),
                )
              else
                ...state.packages.map((package) => _PackageCard(package)),
              const SizedBox(height: 20),
              AppButton(
                label: uxCopy(context, 'احجز الجلسة', 'Book session'),
                isLoading: createState.isSubmitting,
                onPressed: createState.isSubmitting
                    ? null
                    : () => _bookSession(state),
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

  Future<void> _bookSession(CoachDetailsState state) async {
    final session = state.selectedSessionType;
    if (session == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            uxCopy(context, 'اختر نوع الجلسة أولًا', 'Choose a session first'),
          ),
        ),
      );
      return;
    }

    final booking = await ref
        .read(createCoachBookingControllerProvider.notifier)
        .create(
          CreateCoachBookingRequest(
            coachProviderId: widget.coachId,
            sessionTypeId: session.id,
            availabilitySlotId: state.selectedSlot?.id,
            patientGoal: _goalController.text,
          ),
        );
    if (!mounted || booking == null) return;
    if (booking.paymentId != null) {
      context.go(
        RouteNames.payment(booking.paymentId!, coachBookingId: booking.id),
      );
      return;
    }
    context.go(RouteNames.coachBookingDetails(booking.id));
  }

  Future<void> _bookFirstSession(CoachDetailsState state) async {
    if (state.sessionTypes.isEmpty) return;
    final session = state.sessionTypes.first;
    final slot = state.availability.isNotEmpty
        ? state.availability.first
        : null;
    final booking = await ref
        .read(createCoachBookingControllerProvider.notifier)
        .create(
          CreateCoachBookingRequest(
            coachProviderId: widget.coachId,
            sessionTypeId: session.id,
            availabilitySlotId: slot?.id,
            patientGoal: _goalController.text,
          ),
        );
    if (!mounted || booking == null) return;
    if (booking.paymentId != null) {
      context.go(
        RouteNames.payment(booking.paymentId!, coachBookingId: booking.id),
      );
      return;
    }
    context.go(RouteNames.coachBookingDetails(booking.id));
  }
}

class _CoachHeader extends StatelessWidget {
  const _CoachHeader({required this.coach});

  final Coach coach;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  color: AppColors.medicalMint,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.sports_handball_outlined,
                  color: AppColors.primaryDark,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      coach.name(isArabic),
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      coachTypeLabel(context, coach.coachType ?? coach.type),
                      style: const TextStyle(color: AppColors.muted),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (coach.description(isArabic) != null) ...[
            const SizedBox(height: 12),
            Text(coach.description(isArabic)!),
          ],
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              if (coach.experienceYears != null)
                FitnessBadge(
                  label: uxCopy(
                    context,
                    '${coach.experienceYears} سنة خبرة',
                    '${coach.experienceYears} years',
                  ),
                ),
              if (coach.onlineCoachingEnabled)
                FitnessBadge(label: uxCopy(context, 'أونلاين', 'Online')),
              if (coach.gymVisitEnabled)
                FitnessBadge(label: uxCopy(context, 'داخل الجيم', 'Gym visit')),
              if (coach.homeTrainingEnabled)
                FitnessBadge(label: uxCopy(context, 'منزلي', 'Home')),
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

class _QuickCoachBookingCard extends StatelessWidget {
  const _QuickCoachBookingCard({
    required this.firstSession,
    required this.firstSlot,
    required this.isLoading,
    required this.onBook,
  });

  final CoachSessionType firstSession;
  final CoachAvailabilitySlot? firstSlot;
  final bool isLoading;
  final VoidCallback? onBook;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            uxCopy(context, 'حجز سريع', 'Quick booking'),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          Text(
            uxCopy(
              context,
              firstSlot == null
                  ? 'ابدأ بأول جلسة متاحة. السعر النهائي يتأكد من الباك إند.'
                  : 'ابدأ بأول جلسة وأول موعد متاح. السعر النهائي يتأكد من الباك إند.',
              firstSlot == null
                  ? 'Start with the first available session. The backend confirms the price.'
                  : 'Start with the first available session and slot. The backend confirms the price.',
            ),
            style: const TextStyle(color: AppColors.muted),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: AppButton(
              label: uxCopy(
                context,
                'احجز ${firstSession.name(isArabic)} - ${fitnessMoney(firstSession.price)}',
                'Book ${firstSession.name(isArabic)} - ${fitnessMoney(firstSession.price)}',
              ),
              isLoading: isLoading,
              onPressed: onBook,
            ),
          ),
          if (firstSlot != null) ...[
            const SizedBox(height: 8),
            Text(
              dateTimeLabel(firstSlot!.startsAt),
              style: const TextStyle(color: AppColors.muted),
            ),
          ],
        ],
      ),
    );
  }
}

class _SessionTypeCard extends StatelessWidget {
  const _SessionTypeCard({
    required this.session,
    required this.selected,
    required this.onTap,
  });

  final CoachSessionType session;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: onTap,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    session.name(isArabic),
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                if (selected)
                  const Icon(Icons.check_circle, color: AppColors.primaryDark),
              ],
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                FitnessBadge(
                  label: uxCopy(
                    context,
                    '${session.durationMinutes} دقيقة',
                    '${session.durationMinutes} min',
                  ),
                ),
                FitnessBadge(
                  label: sessionModeLabel(context, session.sessionMode),
                ),
                FitnessBadge(label: fitnessMoney(session.price)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _PackageCard extends StatelessWidget {
  const _PackageCard(this.package);

  final CoachPackage package;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            package.name(isArabic),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FitnessBadge(
                label: uxCopy(
                  context,
                  '${package.sessionsCount} جلسة',
                  '${package.sessionsCount} sessions',
                ),
              ),
              if (package.durationDays != null)
                FitnessBadge(
                  label: uxCopy(
                    context,
                    '${package.durationDays} يوم',
                    '${package.durationDays} days',
                  ),
                ),
              FitnessBadge(label: fitnessMoney(package.price)),
            ],
          ),
        ],
      ),
    );
  }
}
