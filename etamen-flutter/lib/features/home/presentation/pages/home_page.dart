import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/account/presentation/pages/account_page.dart';
import 'package:etamen_app/features/appointments/presentation/pages/my_appointments_page.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';

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
    final titles = [
      l10n.get('home'),
      l10n.get('myAppointments'),
      uxCopy(context, 'الخدمات', 'Services'),
      uxCopy(context, 'المتابعة الصحية', 'Health'),
      l10n.get('account'),
    ];
    final pages = [
      HomeDashboardTab(onOpenTab: (value) => setState(() => _index = value)),
      const MyAppointmentsPage(showAppBar: false),
      const ServicesTab(),
      const HealthHubTab(),
      const AccountPage(showAppBar: false),
    ];

    return Scaffold(
      backgroundColor: AppColors.cream,
      appBar: MainShellTopBar(title: titles[_index]),
      body: SafeArea(child: pages[_index]),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        height: 72,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: [
          NavigationDestination(
            icon: const Icon(Icons.home_outlined),
            selectedIcon: const Icon(Icons.home),
            label: l10n.get('home'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.event_note_outlined),
            selectedIcon: const Icon(Icons.event_note),
            label: l10n.get('myAppointments'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.medical_services_outlined),
            selectedIcon: const Icon(Icons.medical_services),
            label: uxCopy(context, 'الخدمات', 'Services'),
          ),
          NavigationDestination(
            icon: const Icon(Icons.health_and_safety_outlined),
            selectedIcon: const Icon(Icons.health_and_safety),
            label: uxCopy(context, 'صحتي', 'Health'),
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
