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
      backgroundColor: AppColors.pageBackground,
      appBar: _index == 0 ? null : MainShellTopBar(title: titles[_index]),
      body: _index == 0 ? pages[_index] : SafeArea(child: pages[_index]),
      bottomNavigationBar: _LegacyBottomNav(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        items: [
          _LegacyNavItem(
            icon: const Icon(Icons.home_outlined),
            selectedIcon: const Icon(Icons.home),
            label: l10n.get('home'),
          ),
          _LegacyNavItem(
            icon: const Icon(Icons.event_note_outlined),
            selectedIcon: const Icon(Icons.event_note),
            label: l10n.get('myAppointments'),
          ),
          _LegacyNavItem(
            icon: const Icon(Icons.medical_services_outlined),
            selectedIcon: const Icon(Icons.medical_services),
            label: uxCopy(context, 'الخدمات', 'Services'),
          ),
          _LegacyNavItem(
            icon: const Icon(Icons.health_and_safety_outlined),
            selectedIcon: const Icon(Icons.health_and_safety),
            label: uxCopy(context, 'صحتي', 'Health'),
          ),
          _LegacyNavItem(
            icon: const Icon(Icons.person_outline),
            selectedIcon: const Icon(Icons.person),
            label: l10n.get('account'),
          ),
        ],
      ),
    );
  }
}

class _LegacyBottomNav extends StatelessWidget {
  const _LegacyBottomNav({
    required this.selectedIndex,
    required this.onDestinationSelected,
    required this.items,
  });

  final int selectedIndex;
  final ValueChanged<int> onDestinationSelected;
  final List<_LegacyNavItem> items;

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Container(
        padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
          boxShadow: [
            BoxShadow(
              color: AppColors.primaryDark.withValues(alpha: 0.12),
              blurRadius: 24,
              offset: const Offset(0, -10),
            ),
          ],
        ),
        child: Row(
          children: [
            for (var i = 0; i < items.length; i++)
              Expanded(
                child: _LegacyNavButton(
                  item: items[i],
                  selected: selectedIndex == i,
                  onTap: () => onDestinationSelected(i),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _LegacyNavItem {
  const _LegacyNavItem({
    required this.icon,
    required this.selectedIcon,
    required this.label,
  });

  final Icon icon;
  final Icon selectedIcon;
  final String label;
}

class _LegacyNavButton extends StatelessWidget {
  const _LegacyNavButton({
    required this.item,
    required this.selected,
    required this.onTap,
  });

  final _LegacyNavItem item;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = selected ? AppColors.medicalAccentDark : Colors.grey.shade500;
    return Semantics(
      selected: selected,
      button: true,
      label: item.label,
      child: InkWell(
        borderRadius: BorderRadius.circular(18),
        onTap: onTap,
        child: AnimatedContainer(
          key: ValueKey('legacy_nav_${item.label}'),
          duration: const Duration(milliseconds: 180),
          height: 56,
          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
          decoration: BoxDecoration(
            color: Colors.transparent,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              IconTheme(
                data: IconThemeData(color: color, size: 22),
                child: selected ? item.selectedIcon : item.icon,
              ),
              const SizedBox(height: 4),
              Text(
                item.label,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: Theme.of(context).textTheme.labelSmall?.copyWith(
                  color: color,
                  fontWeight: selected ? FontWeight.w900 : FontWeight.w600,
                  fontSize: 10.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
