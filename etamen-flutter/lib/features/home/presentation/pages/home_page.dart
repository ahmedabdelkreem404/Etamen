import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/appointments/presentation/pages/my_appointments_page.dart';
import 'package:etamen_app/features/doctors/presentation/pages/doctors_list_page.dart';
import 'package:etamen_app/features/health/presentation/pages/health_dashboard_page.dart';
import 'package:etamen_app/features/labs/presentation/pages/labs_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/pharmacies_page.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _index = 0;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final pages = [
      const DoctorsListPage(showAppBar: false),
      const MyAppointmentsPage(showAppBar: false),
      const PharmaciesPage(showAppBar: false),
      const LabsPage(showAppBar: false),
      const HealthDashboardPage(showAppBar: false),
    ];

    return Scaffold(
      body: SafeArea(child: pages[_index]),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) {
          if (value == 5) {
            context.go(RouteNames.account);
            return;
          }
          setState(() => _index = value);
        },
        destinations: [
          NavigationDestination(
            icon: const Icon(Icons.medical_services_outlined),
            selectedIcon: const Icon(Icons.medical_services),
            label: l10n.get('doctors'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.event_note_outlined),
            selectedIcon: const Icon(Icons.event_note),
            label: l10n.get('myAppointments'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.local_pharmacy_outlined),
            selectedIcon: const Icon(Icons.local_pharmacy),
            label: l10n.get('pharmacies'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.biotech_outlined),
            selectedIcon: const Icon(Icons.biotech),
            label: l10n.get('labs'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.health_and_safety_outlined),
            selectedIcon: const Icon(Icons.health_and_safety),
            label: l10n.get('health'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.person_outline),
            selectedIcon: const Icon(Icons.person),
            label: l10n.get('account'),
          ),
        ],
      ),
    );
  }
}
