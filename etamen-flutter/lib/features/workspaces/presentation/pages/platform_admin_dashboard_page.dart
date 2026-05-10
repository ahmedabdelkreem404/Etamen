import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/logout_button.dart';
import 'package:etamen_app/features/workspaces/presentation/widgets/workspace_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class PlatformAdminDashboardPage extends ConsumerWidget {
  const PlatformAdminDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return AppScaffold(
      title: 'إدارة المنصة',
      actions: [
        IconButton(
          tooltip: 'تبديل مساحة العمل',
          onPressed: () => showWorkspaceSwitcher(context, ref),
          icon: const Icon(Icons.swap_horiz),
        ),
      ],
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: const [
          Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'لوحة إدارة المنصة',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
                  ),
                  SizedBox(height: 8),
                  Text(
                    'هذا shell محلي فقط لربط مساحات العمل. إدارة Filament تظل مصدر التشغيل الكامل حاليًا.',
                  ),
                ],
              ),
            ),
          ),
          SizedBox(height: 12),
          Card(
            child: ListTile(
              leading: Icon(Icons.verified_user_outlined),
              title: Text('مراجعات الدفع واعتماد المزودين'),
              subtitle: Text('سيتم تفعيلها لاحقًا داخل لوحة تشغيل المنصة.'),
            ),
          ),
          SizedBox(height: 12),
          LogoutButton(),
        ],
      ),
    );
  }
}
