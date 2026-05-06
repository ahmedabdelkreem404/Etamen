import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class AppointmentResultPage extends StatelessWidget {
  const AppointmentResultPage({required this.appointmentId, super.key});

  final int appointmentId;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return AppScaffold(
      title: l10n.get('bookAppointment'),
      body: Center(
        child: Card(
          margin: const EdgeInsets.all(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text('${l10n.get('bookingConfirmed')} #$appointmentId'),
                const SizedBox(height: 16),
                AppButton(
                  label: l10n.get('viewAppointment'),
                  onPressed: () =>
                      context.go(RouteNames.appointmentDetails(appointmentId)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
