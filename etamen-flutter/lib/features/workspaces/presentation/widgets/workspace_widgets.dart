import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/workspace_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class WorkspaceAccountSection extends ConsumerWidget {
  const WorkspaceAccountSection({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final state = ref.watch(workspaceControllerProvider);
    final selected = state.selectedWorkspace;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                const Icon(Icons.workspaces_outline, color: AppColors.primary),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'تبديل مساحة العمل',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                if (state.isLoading)
                  const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              selected == null
                  ? 'يتم تحميل المساحات المتاحة من السيرفر.'
                  : '${selected.label(isArabic)} - ${selected.typeLabel(isArabic)}',
              style: Theme.of(
                context,
              ).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade700),
            ),
            if (state.error != null) ...[
              const SizedBox(height: 8),
              Text(
                state.error!.message,
                style: Theme.of(
                  context,
                ).textTheme.bodySmall?.copyWith(color: Colors.red.shade700),
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      await ref
                          .read(workspaceControllerProvider.notifier)
                          .load();
                    },
                    icon: const Icon(Icons.refresh),
                    label: const Text('تحديث'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: FilledButton.icon(
                    onPressed: state.workspaces.isEmpty
                        ? null
                        : () => showWorkspaceSwitcher(context, ref),
                    icon: const Icon(Icons.swap_horiz),
                    label: const Text('تبديل'),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

Future<void> showWorkspaceSwitcher(BuildContext context, WidgetRef ref) {
  final isArabic = AppLocalizations.of(context).isArabic;
  if (ref.read(workspaceControllerProvider).workspaces.isEmpty) {
    ref.read(workspaceControllerProvider.notifier).load();
  }

  return showModalBottomSheet<void>(
    context: context,
    showDragHandle: true,
    builder: (sheetContext) {
      return Consumer(
        builder: (context, innerRef, _) {
          final state = innerRef.watch(workspaceControllerProvider);
          return SafeArea(
            child: ListView(
              shrinkWrap: true,
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
              children: [
                Text(
                  'اختر مساحة العمل',
                  style: Theme.of(
                    sheetContext,
                  ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 12),
                for (final workspace in state.workspaces)
                  _WorkspaceTile(
                    workspace: workspace,
                    selected: workspace.key == state.selectedWorkspace?.key,
                    isArabic: isArabic,
                    onTap: () async {
                      await innerRef
                          .read(workspaceControllerProvider.notifier)
                          .switchTo(workspace.key);
                      if (!sheetContext.mounted) return;
                      Navigator.of(sheetContext).pop();
                      if (!context.mounted) return;
                      _openWorkspace(context, workspace);
                    },
                  ),
              ],
            ),
          );
        },
      );
    },
  );
}

void _openWorkspace(BuildContext context, WorkspaceSummary workspace) {
  if (workspace.isProvider && workspace.providerId != null) {
    context.push(RouteNames.providerDashboard(workspace.providerId!));
    return;
  }
  if (workspace.isPlatformAdmin) {
    context.push(RouteNames.platformAdminDashboard);
    return;
  }
  context.go(RouteNames.home);
}

class _WorkspaceTile extends StatelessWidget {
  const _WorkspaceTile({
    required this.workspace,
    required this.selected,
    required this.isArabic,
    required this.onTap,
  });

  final WorkspaceSummary workspace;
  final bool selected;
  final bool isArabic;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        leading: Icon(
          workspace.isProvider
              ? Icons.business_center_outlined
              : workspace.isPlatformAdmin
              ? Icons.admin_panel_settings_outlined
              : Icons.person_outline,
          color: selected ? AppColors.primary : null,
        ),
        title: Text(
          workspace.label(isArabic),
          style: const TextStyle(fontWeight: FontWeight.w800),
        ),
        subtitle: Text(workspace.typeLabel(isArabic)),
        trailing: selected
            ? const Icon(Icons.check_circle, color: AppColors.primary)
            : const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}
