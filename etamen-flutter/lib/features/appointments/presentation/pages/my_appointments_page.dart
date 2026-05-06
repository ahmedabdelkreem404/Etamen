import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/appointments/presentation/providers/my_appointments_controller.dart';
import 'package:etamen_app/features/appointments/presentation/widgets/appointment_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MyAppointmentsPage extends ConsumerWidget {
  const MyAppointmentsPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(myAppointmentsControllerProvider);
    final controller = ref.read(myAppointmentsControllerProvider.notifier);
    final body = RefreshIndicator(
      onRefresh: () => controller.load(refresh: true),
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            l10n.get('myAppointments'),
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 12),
          _FilterChips(
            selected: state.selectedFilter,
            onSelected: controller.selectFilter,
          ),
          const SizedBox(height: 12),
          if (state.isLoading)
            const LoadingView()
          else if (state.error != null)
            ErrorView(
              message: state.error!.message,
              onRetry: () => controller.load(),
            )
          else if (state.isEmpty)
            EmptyView(
              message: l10n.get('emptyAppointments'),
              icon: Icons.event_busy,
            )
          else
            ...state.filteredItems.map(
              (appointment) => AppointmentCard(appointment: appointment),
            ),
          if (state.isEmpty) ...[
            const SizedBox(height: 16),
            AppButton(
              label: l10n.get('bookAppointment'),
              onPressed: () => context.go(RouteNames.doctors),
            ),
          ],
        ],
      ),
    );

    if (!showAppBar) return body;
    return AppScaffold(title: l10n.get('myAppointments'), body: body);
  }
}

class _FilterChips extends StatelessWidget {
  const _FilterChips({required this.selected, required this.onSelected});

  final AppointmentListFilter selected;
  final ValueChanged<AppointmentListFilter> onSelected;

  @override
  Widget build(BuildContext context) {
    final filters = AppointmentListFilter.values;
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: filters
            .map(
              (filter) => Padding(
                padding: const EdgeInsetsDirectional.only(end: 8),
                child: FilterChip(
                  selected: selected == filter,
                  label: Text(_label(context, filter)),
                  onSelected: (_) => onSelected(filter),
                ),
              ),
            )
            .toList(),
      ),
    );
  }

  String _label(BuildContext context, AppointmentListFilter filter) {
    final l10n = AppLocalizations.of(context);
    return switch (filter) {
      AppointmentListFilter.all => l10n.get('all'),
      AppointmentListFilter.upcoming => l10n.get('upcoming'),
      AppointmentListFilter.pendingPayment => l10n.get('pendingPayment'),
      AppointmentListFilter.completed => l10n.get('completed'),
      AppointmentListFilter.cancelled => l10n.get('cancelled'),
    };
  }
}
