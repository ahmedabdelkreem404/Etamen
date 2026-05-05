import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class DoctorsListPage extends ConsumerWidget {
  const DoctorsListPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(doctorsListControllerProvider);
    final content = RefreshIndicator(
      onRefresh: () => ref.read(doctorsListControllerProvider.notifier).load(),
      child: Builder(
        builder: (context) {
          if (state.isLoading) return const LoadingView();
          if (state.error != null) {
            return ErrorView(
              message: state.error!.message,
              onRetry: () =>
                  ref.read(doctorsListControllerProvider.notifier).load(),
            );
          }
          if (state.isEmpty) {
            return EmptyView(message: l10n.get('emptyDoctors'));
          }

          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16),
            children: [
              if (!showAppBar) ...[
                Text(
                  l10n.get('doctors'),
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 16),
              ],
              for (final doctor in state.doctors)
                DoctorCard(
                  doctor: doctor,
                  onTap: () => context.go(RouteNames.doctorProfile(doctor.id)),
                ),
            ],
          );
        },
      ),
    );

    if (!showAppBar) return content;

    return AppScaffold(title: l10n.get('doctors'), body: content);
  }
}
