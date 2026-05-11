import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/logout_button.dart';
import 'package:etamen_app/features/admin_operations/data/admin_operation_models.dart';
import 'package:etamen_app/features/admin_operations/presentation/providers/admin_operations_providers.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/workspace_providers.dart';
import 'package:etamen_app/features/workspaces/presentation/widgets/workspace_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AdminOperationsDashboardPage extends ConsumerWidget {
  const AdminOperationsDashboardPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(adminDashboardProvider);
    return AppScaffold(
      title: 'إدارة المنصة',
      actions: [
        IconButton(
          tooltip: 'تبديل مساحة العمل',
          onPressed: () => showWorkspaceSwitcher(context, ref),
          icon: const Icon(Icons.swap_horiz),
        ),
      ],
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(adminDashboardProvider.future),
        child: state.when(
          loading: () => const _LoadingList(),
          error: (error, _) => _ErrorList(
            message: errorMessage(error),
            onRetry: () => ref.invalidate(adminDashboardProvider),
          ),
          data: (dashboard) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _HeroCard(
                title: 'مركز عمليات اطمن',
                body:
                    'مراجعة المدفوعات والمزودين والدعم والاسترداد والنزاعات من مصدر صلاحيات الباك إند.',
                icon: Icons.admin_panel_settings_outlined,
              ),
              const SizedBox(height: 12),
              _DashboardGrid(dashboard: dashboard),
              const SizedBox(height: 16),
              Text(
                'إجراءات سريعة',
                style: Theme.of(
                  context,
                ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 8),
              for (final action in dashboard.quickActions)
                Card(
                  child: ListTile(
                    leading: Icon(_quickActionIcon(action.key)),
                    title: Text(action.label(true)),
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => context.push(_quickActionRoute(action.key)),
                  ),
                ),
              const SizedBox(height: 16),
              Text(
                'آخر الأحداث',
                style: Theme.of(
                  context,
                ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 8),
              if (dashboard.recentEvents.isEmpty)
                const _EmptyCard(message: 'لا توجد أحداث حديثة.')
              else
                for (final event in dashboard.recentEvents)
                  _OperationTile(
                    item: event,
                    onTap: () => context.push(RouteNames.adminAuditLog),
                  ),
              const SizedBox(height: 12),
              const LogoutButton(),
            ],
          ),
        ),
      ),
    );
  }
}

class AdminPaymentReviewQueuePage extends StatelessWidget {
  const AdminPaymentReviewQueuePage({super.key});

  @override
  Widget build(BuildContext context) => const AdminOperationListPage(
    title: 'مراجعة المدفوعات',
    section: 'payments/pending',
    detailSection: 'payments',
    emptyMessage: 'لا توجد مدفوعات في انتظار المراجعة.',
  );
}

class AdminProviderApprovalQueuePage extends StatelessWidget {
  const AdminProviderApprovalQueuePage({super.key});

  @override
  Widget build(BuildContext context) => const AdminOperationListPage(
    title: 'موافقات المزودين',
    section: 'providers/pending',
    detailSection: 'providers',
    emptyMessage: 'لا توجد مزودين قيد الموافقة.',
  );
}

class AdminSupportTicketsPage extends StatelessWidget {
  const AdminSupportTicketsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminOperationListPage(
    title: 'تذاكر الدعم',
    section: 'support/tickets',
    detailSection: 'support-tickets',
    emptyMessage: 'لا توجد تذاكر دعم.',
  );
}

class AdminRefundRequestsPage extends StatelessWidget {
  const AdminRefundRequestsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminOperationListPage(
    title: 'طلبات الاسترداد',
    section: 'refunds',
    detailSection: 'refunds',
    emptyMessage: 'لا توجد طلبات استرداد.',
  );
}

class AdminDisputesPage extends StatelessWidget {
  const AdminDisputesPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminOperationListPage(
    title: 'النزاعات',
    section: 'disputes',
    detailSection: 'disputes',
    emptyMessage: 'لا توجد نزاعات مفتوحة.',
  );
}

class AdminAuditLogPage extends StatelessWidget {
  const AdminAuditLogPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminOperationListPage(
    title: 'سجل العمليات',
    section: 'audit-log',
    detailSection: 'audit-log',
    emptyMessage: 'لا توجد أحداث في السجل.',
    openDetails: false,
  );
}

class AdminOperationListPage extends ConsumerWidget {
  const AdminOperationListPage({
    required this.title,
    required this.section,
    required this.detailSection,
    required this.emptyMessage,
    this.openDetails = true,
    super.key,
  });

  final String title;
  final String section;
  final String detailSection;
  final String emptyMessage;
  final bool openDetails;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(adminListProvider(section));
    return AppScaffold(
      title: title,
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(adminListProvider(section).future),
        child: state.when(
          loading: () => const _LoadingList(),
          error: (error, _) => _ErrorList(
            message: errorMessage(error),
            onRetry: () => ref.invalidate(adminListProvider(section)),
          ),
          data: (response) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (response.items.isEmpty)
                _EmptyCard(message: emptyMessage)
              else
                for (final item in response.items)
                  _OperationTile(
                    item: item,
                    onTap: openDetails
                        ? () => context.push(
                            _adminDetailsRoute(detailSection, item.id),
                          )
                        : null,
                  ),
            ],
          ),
        ),
      ),
    );
  }
}

class AdminPaymentReviewDetailsPage extends StatelessWidget {
  const AdminPaymentReviewDetailsPage({required this.id, super.key});

  final int id;

  @override
  Widget build(BuildContext context) =>
      AdminOperationDetailsPage(section: 'payments', id: id);
}

class AdminProviderDetailsPage extends StatelessWidget {
  const AdminProviderDetailsPage({required this.id, super.key});

  final int id;

  @override
  Widget build(BuildContext context) =>
      AdminOperationDetailsPage(section: 'providers', id: id);
}

class AdminSupportTicketDetailsPage extends StatelessWidget {
  const AdminSupportTicketDetailsPage({required this.id, super.key});

  final int id;

  @override
  Widget build(BuildContext context) =>
      AdminOperationDetailsPage(section: 'support/tickets', id: id);
}

class AdminRefundDetailsPage extends StatelessWidget {
  const AdminRefundDetailsPage({required this.id, super.key});

  final int id;

  @override
  Widget build(BuildContext context) =>
      AdminOperationDetailsPage(section: 'refunds', id: id);
}

class AdminDisputeDetailsPage extends StatelessWidget {
  const AdminDisputeDetailsPage({required this.id, super.key});

  final int id;

  @override
  Widget build(BuildContext context) =>
      AdminOperationDetailsPage(section: 'disputes', id: id);
}

class AdminOperationDetailsPage extends ConsumerWidget {
  const AdminOperationDetailsPage({
    required this.section,
    required this.id,
    super.key,
  });

  final String section;
  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final params = AdminDetailParams(section: section, id: id);
    final state = ref.watch(adminDetailsProvider(params));
    return AppScaffold(
      title: _detailsTitle(section),
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(adminDetailsProvider(params).future),
        child: state.when(
          loading: () => const _LoadingList(),
          error: (error, _) => _ErrorList(
            message: errorMessage(error),
            onRetry: () => ref.invalidate(adminDetailsProvider(params)),
          ),
          data: (item) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _HeroCard(
                title: item.title(true),
                body: item.subtitle(true),
                icon: _sectionIcon(section),
              ),
              const SizedBox(height: 12),
              _DetailsCard(raw: item.raw),
              const SizedBox(height: 12),
              _AdminActionPanel(section: section, id: id),
            ],
          ),
        ),
      ),
    );
  }
}

class UserSupportTicketsPage extends ConsumerWidget {
  const UserSupportTicketsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(supportTicketsProvider);
    return AppScaffold(
      title: 'الدعم',
      body: RefreshIndicator(
        onRefresh: () => ref.refresh(supportTicketsProvider.future),
        child: state.when(
          loading: () => const _LoadingList(),
          error: (error, _) => _ErrorList(
            message: errorMessage(error),
            onRetry: () => ref.invalidate(supportTicketsProvider),
          ),
          data: (response) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _HeroCard(
                title: 'تذاكر الدعم',
                body:
                    'تابع طلبات الدعم بدون مشاركة أي بيانات حساسة داخل الرسائل.',
                icon: Icons.support_agent,
              ),
              const SizedBox(height: 12),
              FilledButton.icon(
                onPressed: () => context.push(RouteNames.createSupportTicket),
                icon: const Icon(Icons.add),
                label: const Text('إنشاء تذكرة دعم'),
              ),
              const SizedBox(height: 8),
              OutlinedButton.icon(
                onPressed: () => context.push(RouteNames.refunds),
                icon: const Icon(Icons.payments_outlined),
                label: const Text('طلبات الاسترداد'),
              ),
              OutlinedButton.icon(
                onPressed: () => context.push(RouteNames.disputes),
                icon: const Icon(Icons.report_problem_outlined),
                label: const Text('النزاعات'),
              ),
              const SizedBox(height: 12),
              if (response.items.isEmpty)
                const _EmptyCard(message: 'لا توجد تذاكر دعم بعد.')
              else
                for (final ticket in response.items)
                  _OperationTile(
                    item: ticket,
                    onTap: () => context.push(
                      RouteNames.supportTicketDetails(ticket.id),
                    ),
                  ),
            ],
          ),
        ),
      ),
    );
  }
}

class UserCreateSupportTicketPage extends ConsumerStatefulWidget {
  const UserCreateSupportTicketPage({super.key});

  @override
  ConsumerState<UserCreateSupportTicketPage> createState() =>
      _UserCreateSupportTicketPageState();
}

class _UserCreateSupportTicketPageState
    extends ConsumerState<UserCreateSupportTicketPage> {
  final _subject = TextEditingController();
  final _description = TextEditingController();
  String _category = 'technical';
  bool _isSaving = false;

  @override
  void dispose() {
    _subject.dispose();
    _description.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AppScaffold(
      title: 'إنشاء تذكرة دعم',
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          DropdownButtonFormField<String>(
            value: _category,
            decoration: const InputDecoration(labelText: 'نوع التذكرة'),
            items: const [
              DropdownMenuItem(value: 'payment', child: Text('دفع')),
              DropdownMenuItem(value: 'booking', child: Text('حجز')),
              DropdownMenuItem(value: 'provider', child: Text('مزود')),
              DropdownMenuItem(value: 'technical', child: Text('تقني')),
              DropdownMenuItem(value: 'refund', child: Text('استرداد')),
              DropdownMenuItem(value: 'other', child: Text('أخرى')),
            ],
            onChanged: (value) =>
                setState(() => _category = value ?? _category),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _subject,
            decoration: const InputDecoration(labelText: 'العنوان'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _description,
            minLines: 4,
            maxLines: 8,
            decoration: const InputDecoration(
              labelText: 'التفاصيل',
              alignLabelWithHint: true,
            ),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: _isSaving ? null : _submit,
            icon: _isSaving
                ? const SizedBox.square(
                    dimension: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.send_outlined),
            label: const Text('إرسال التذكرة'),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    if (_subject.text.trim().isEmpty || _description.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('اكتب العنوان والتفاصيل أولا.')),
      );
      return;
    }

    setState(() => _isSaving = true);
    final workspace = ref.read(workspaceControllerProvider).selectedWorkspace;
    final result = await ref
        .read(adminOperationsRepositoryProvider)
        .createSupportTicket(
          category: _category,
          subject: _subject.text.trim(),
          description: _description.text.trim(),
          providerId: workspace?.isProvider == true
              ? workspace?.providerId
              : null,
        );
    setState(() => _isSaving = false);
    if (!mounted) return;

    result.when(
      success: (ticket) {
        ref.invalidate(supportTicketsProvider);
        context.go(RouteNames.supportTicketDetails(ticket.id));
      },
      failure: (failure) => ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(failure.error.message))),
    );
  }
}

class UserSupportTicketDetailsPage extends ConsumerWidget {
  const UserSupportTicketDetailsPage({required this.id, super.key});

  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final future = ref
        .watch(adminOperationsRepositoryProvider)
        .supportTicket(id)
        .then(
          (result) => result.when(
            success: (ticket) => ticket,
            failure: (failure) => throw failure.error,
          ),
        );
    return _FutureItemPage(title: 'تفاصيل التذكرة', future: future);
  }
}

class UserRefundRequestsPage extends StatelessWidget {
  const UserRefundRequestsPage({super.key});

  @override
  Widget build(BuildContext context) => const _UserListPage(
    title: 'طلبات الاسترداد',
    kind: 'refunds',
    createRoute: RouteNames.createRefund,
    emptyMessage: 'لا توجد طلبات استرداد.',
  );
}

class UserCreateRefundPage extends StatefulWidget {
  const UserCreateRefundPage({super.key});

  @override
  State<UserCreateRefundPage> createState() => _UserCreateRefundPageState();
}

class _UserCreateRefundPageState extends State<UserCreateRefundPage> {
  final _amount = TextEditingController();
  final _reason = TextEditingController();
  bool _isSaving = false;

  @override
  void dispose() {
    _amount.dispose();
    _reason.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => _SimpleRequestForm(
    title: 'طلب استرداد',
    amountController: _amount,
    reasonController: _reason,
    isSaving: _isSaving,
    submitLabel: 'إرسال طلب الاسترداد',
    onSubmit: (ref) async {
      final amount = double.tryParse(_amount.text.trim()) ?? 0;
      if (amount <= 0 || _reason.text.trim().isEmpty) return false;
      setState(() => _isSaving = true);
      final result = await ref
          .read(adminOperationsRepositoryProvider)
          .createRefund(reason: _reason.text.trim(), amount: amount);
      setState(() => _isSaving = false);
      return result.when(success: (_) => true, failure: (_) => false);
    },
    successRoute: RouteNames.refunds,
  );
}

class UserDisputesPage extends StatelessWidget {
  const UserDisputesPage({super.key});

  @override
  Widget build(BuildContext context) => const _UserListPage(
    title: 'النزاعات',
    kind: 'disputes',
    createRoute: RouteNames.createDispute,
    emptyMessage: 'لا توجد نزاعات.',
  );
}

class UserCreateDisputePage extends StatefulWidget {
  const UserCreateDisputePage({super.key});

  @override
  State<UserCreateDisputePage> createState() => _UserCreateDisputePageState();
}

class _UserCreateDisputePageState extends State<UserCreateDisputePage> {
  final _reason = TextEditingController();
  bool _isSaving = false;

  @override
  void dispose() {
    _reason.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => _SimpleRequestForm(
    title: 'فتح نزاع',
    reasonController: _reason,
    isSaving: _isSaving,
    submitLabel: 'إرسال النزاع',
    onSubmit: (ref) async {
      if (_reason.text.trim().isEmpty) return false;
      setState(() => _isSaving = true);
      final result = await ref
          .read(adminOperationsRepositoryProvider)
          .createDispute(reason: _reason.text.trim());
      setState(() => _isSaving = false);
      return result.when(success: (_) => true, failure: (_) => false);
    },
    successRoute: RouteNames.disputes,
  );
}

class _UserListPage extends ConsumerWidget {
  const _UserListPage({
    required this.title,
    required this.kind,
    required this.createRoute,
    required this.emptyMessage,
  });

  final String title;
  final String kind;
  final String createRoute;
  final String emptyMessage;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final repo = ref.watch(adminOperationsRepositoryProvider);
    final future = kind == 'refunds' ? repo.refunds() : repo.disputes();
    return AppScaffold(
      title: title,
      body: FutureBuilder(
        future: future,
        builder: (context, snapshot) {
          if (!snapshot.hasData) return const _LoadingList();
          final result = snapshot.data!;
          return result.when(
            success: (response) => ListView(
              padding: const EdgeInsets.all(16),
              children: [
                FilledButton.icon(
                  onPressed: () => context.push(createRoute),
                  icon: const Icon(Icons.add),
                  label: Text(title),
                ),
                const SizedBox(height: 12),
                if (response.items.isEmpty)
                  _EmptyCard(message: emptyMessage)
                else
                  for (final item in response.items) _OperationTile(item: item),
              ],
            ),
            failure: (failure) => _ErrorList(message: failure.error.message),
          );
        },
      ),
    );
  }
}

class _SimpleRequestForm extends ConsumerWidget {
  const _SimpleRequestForm({
    required this.title,
    required this.reasonController,
    required this.isSaving,
    required this.submitLabel,
    required this.onSubmit,
    required this.successRoute,
    this.amountController,
  });

  final String title;
  final TextEditingController? amountController;
  final TextEditingController reasonController;
  final bool isSaving;
  final String submitLabel;
  final Future<bool> Function(WidgetRef ref) onSubmit;
  final String successRoute;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return AppScaffold(
      title: title,
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (amountController != null) ...[
            TextField(
              controller: amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: 'المبلغ'),
            ),
            const SizedBox(height: 12),
          ],
          TextField(
            controller: reasonController,
            minLines: 4,
            maxLines: 8,
            decoration: const InputDecoration(labelText: 'السبب'),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: isSaving
                ? null
                : () async {
                    final ok = await onSubmit(ref);
                    if (!context.mounted) return;
                    if (ok) {
                      context.go(successRoute);
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('راجع البيانات وحاول تاني.'),
                        ),
                      );
                    }
                  },
            icon: const Icon(Icons.send_outlined),
            label: Text(submitLabel),
          ),
        ],
      ),
    );
  }
}

class _FutureItemPage extends StatelessWidget {
  const _FutureItemPage({required this.title, required this.future});

  final String title;
  final Future<AdminListItem> future;

  @override
  Widget build(BuildContext context) {
    return AppScaffold(
      title: title,
      body: FutureBuilder<AdminListItem>(
        future: future,
        builder: (context, snapshot) {
          if (!snapshot.hasData) return const _LoadingList();
          final item = snapshot.data!;
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _HeroCard(
                title: item.title(true),
                body: item.subtitle(true),
                icon: Icons.support_agent,
              ),
              const SizedBox(height: 12),
              _DetailsCard(raw: item.raw),
            ],
          );
        },
      ),
    );
  }
}

class _AdminActionPanel extends ConsumerWidget {
  const _AdminActionPanel({required this.section, required this.id});

  final String section;
  final int id;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final actions = _actionsFor(section);
    if (actions.isEmpty) {
      return const _EmptyCard(message: 'هذه الصفحة للعرض فقط في هذا الإصدار.');
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (final action in actions)
          Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: OutlinedButton.icon(
              onPressed: () => _runAction(context, ref, action),
              icon: Icon(action.icon),
              label: Text(action.label),
            ),
          ),
      ],
    );
  }

  Future<void> _runAction(
    BuildContext context,
    WidgetRef ref,
    _AdminAction action,
  ) async {
    final note = action.requiresNote
        ? await _askForNote(context, action.noteLabel)
        : null;
    if (action.requiresNote && (note == null || note.trim().isEmpty)) return;
    if (!context.mounted) return;

    final confirmed = await _confirmAdminAction(context, action.label);
    if (confirmed != true) return;

    final data = <String, dynamic>{};
    if (note != null) {
      data[action.noteKey] = note.trim();
    }
    final result = await ref
        .read(adminOperationsRepositoryProvider)
        .adminAction(section, id, action.key, data: data);
    if (!context.mounted) return;
    result.when(
      success: (_) {
        ref.invalidate(
          adminDetailsProvider(AdminDetailParams(section: section, id: id)),
        );
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(const SnackBar(content: Text('تم تنفيذ الإجراء.')));
      },
      failure: (failure) => ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(failure.error.message))),
    );
  }
}

class _AdminAction {
  const _AdminAction({
    required this.key,
    required this.label,
    required this.icon,
    this.requiresNote = false,
    this.noteKey = 'notes',
    this.noteLabel = 'ملاحظة الإدارة',
  });

  final String key;
  final String label;
  final IconData icon;
  final bool requiresNote;
  final String noteKey;
  final String noteLabel;
}

class _DetailsCard extends StatelessWidget {
  const _DetailsCard({required this.raw});

  final Map<String, dynamic> raw;

  @override
  Widget build(BuildContext context) {
    final rows = _flatten(raw).entries
        .where((entry) => !_isUnsafeKey(entry.key) && entry.value != null)
        .take(36)
        .toList(growable: false);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'تفاصيل آمنة',
              style: Theme.of(
                context,
              ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 8),
            for (final row in rows)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 5),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 125,
                      child: Text(
                        row.key.replaceAll('_', ' '),
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
                    Expanded(child: Text('${row.value}')),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _DashboardGrid extends StatelessWidget {
  const _DashboardGrid({required this.dashboard});

  final AdminDashboard dashboard;

  @override
  Widget build(BuildContext context) {
    final labels = {
      'pending_payment_reviews_count': 'مدفوعات للمراجعة',
      'pending_provider_approvals_count': 'مزودون قيد الموافقة',
      'open_support_tickets_count': 'تذاكر مفتوحة',
      'open_refund_requests_count': 'استردادات مفتوحة',
      'unresolved_disputes_count': 'نزاعات مفتوحة',
      'today_appointments_count': 'حجوزات أطباء اليوم',
      'today_radiology_orders_count': 'طلبات أشعة اليوم',
      'today_gym_bookings_count': 'حجوزات جيم اليوم',
      'today_coach_bookings_count': 'حجوزات كوتش اليوم',
    };
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 10,
      mainAxisSpacing: 10,
      childAspectRatio: 1.45,
      children: [
        for (final entry in dashboard.counts.entries)
          Card(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    '${entry.value}',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(labels[entry.key] ?? entry.key),
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class _OperationTile extends StatelessWidget {
  const _OperationTile({required this.item, this.onTap});

  final AdminListItem item;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: ListTile(
        leading: const Icon(Icons.fact_check_outlined),
        title: Text(item.title(AppLocalizations.of(context).isArabic)),
        subtitle: Text(item.subtitle(AppLocalizations.of(context).isArabic)),
        trailing: onTap == null ? null : const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}

class _HeroCard extends StatelessWidget {
  const _HeroCard({
    required this.title,
    required this.body,
    required this.icon,
  });

  final String title;
  final String body;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Icon(icon, size: 36),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(body),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _EmptyCard extends StatelessWidget {
  const _EmptyCard({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) => Card(
    child: Padding(padding: const EdgeInsets.all(16), child: Text(message)),
  );
}

class _LoadingList extends StatelessWidget {
  const _LoadingList();

  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.only(top: 80),
    children: const [Center(child: CircularProgressIndicator())],
  );
}

class _ErrorList extends StatelessWidget {
  const _ErrorList({required this.message, this.onRetry});

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(16),
    children: [
      Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Text(message),
              if (onRetry != null) ...[
                const SizedBox(height: 8),
                OutlinedButton(
                  onPressed: onRetry,
                  child: const Text('إعادة المحاولة'),
                ),
              ],
            ],
          ),
        ),
      ),
    ],
  );
}

Future<String?> _askForNote(BuildContext context, String label) async {
  final controller = TextEditingController();
  return showDialog<String>(
    context: context,
    builder: (context) => AlertDialog(
      title: Text(label),
      content: TextField(
        controller: controller,
        minLines: 3,
        maxLines: 5,
        decoration: const InputDecoration(labelText: 'اكتب الملاحظة'),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('إلغاء'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(controller.text),
          child: const Text('تأكيد'),
        ),
      ],
    ),
  );
}

Future<bool?> _confirmAdminAction(BuildContext context, String label) {
  return showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('تأكيد الإجراء'),
      content: Text('هل تريد تنفيذ "$label"؟'),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('إلغاء'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('تأكيد'),
        ),
      ],
    ),
  );
}

List<_AdminAction> _actionsFor(String section) {
  return switch (section) {
    'payments' => const [
      _AdminAction(
        key: 'accept',
        label: 'قبول الدفع',
        icon: Icons.check_circle,
      ),
      _AdminAction(
        key: 'reject',
        label: 'رفض الدفع',
        icon: Icons.cancel_outlined,
        requiresNote: true,
        noteKey: 'reason',
        noteLabel: 'سبب الرفض',
      ),
    ],
    'providers' => const [
      _AdminAction(
        key: 'approve',
        label: 'اعتماد المزود',
        icon: Icons.verified,
      ),
      _AdminAction(
        key: 'reject',
        label: 'رفض المزود',
        icon: Icons.block,
        requiresNote: true,
      ),
      _AdminAction(
        key: 'suspend',
        label: 'إيقاف المزود',
        icon: Icons.pause_circle_outline,
        requiresNote: true,
      ),
    ],
    'support/tickets' => const [
      _AdminAction(
        key: 'reply',
        label: 'رد على التذكرة',
        icon: Icons.reply,
        requiresNote: true,
        noteKey: 'message',
        noteLabel: 'نص الرد',
      ),
      _AdminAction(
        key: 'internal-note',
        label: 'ملاحظة داخلية',
        icon: Icons.note_add_outlined,
        requiresNote: true,
        noteKey: 'message',
        noteLabel: 'الملاحظة الداخلية',
      ),
      _AdminAction(
        key: 'close',
        label: 'إغلاق التذكرة',
        icon: Icons.lock_outline,
      ),
    ],
    'refunds' => const [
      _AdminAction(
        key: 'mark-under-review',
        label: 'وضع قيد المراجعة',
        icon: Icons.manage_search,
        requiresNote: true,
      ),
      _AdminAction(
        key: 'approve',
        label: 'قبول الاسترداد',
        icon: Icons.check_circle,
        requiresNote: true,
      ),
      _AdminAction(
        key: 'reject',
        label: 'رفض الاسترداد',
        icon: Icons.cancel_outlined,
        requiresNote: true,
      ),
      _AdminAction(
        key: 'mark-processed',
        label: 'تم التنفيذ اليدوي',
        icon: Icons.done_all,
        requiresNote: true,
      ),
    ],
    'disputes' => const [
      _AdminAction(
        key: 'assign',
        label: 'تعيين لي',
        icon: Icons.assignment_ind,
      ),
      _AdminAction(
        key: 'resolve',
        label: 'حل النزاع',
        icon: Icons.task_alt,
        requiresNote: true,
      ),
      _AdminAction(
        key: 'close',
        label: 'إغلاق النزاع',
        icon: Icons.lock_outline,
        requiresNote: true,
      ),
    ],
    _ => const [],
  };
}

String _quickActionRoute(String key) {
  return switch (key) {
    'payment_reviews' => RouteNames.adminPayments,
    'provider_approvals' => RouteNames.adminProviders,
    'support_tickets' => RouteNames.adminSupportTickets,
    'refund_requests' => RouteNames.adminRefunds,
    'disputes' => RouteNames.adminDisputes,
    'audit_log' => RouteNames.adminAuditLog,
    _ => RouteNames.platformAdminDashboard,
  };
}

String _adminDetailsRoute(String section, int id) {
  return switch (section) {
    'payments' => RouteNames.adminOperationDetails('payments', id),
    'providers' => RouteNames.adminOperationDetails('providers', id),
    'support-tickets' => RouteNames.adminOperationDetails(
      'support-tickets',
      id,
    ),
    'refunds' => RouteNames.adminOperationDetails('refunds', id),
    'disputes' => RouteNames.adminOperationDetails('disputes', id),
    _ => RouteNames.adminAuditLog,
  };
}

String _detailsTitle(String section) {
  return switch (section) {
    'payments' => 'تفاصيل الدفع',
    'providers' => 'تفاصيل المزود',
    'support/tickets' => 'تفاصيل التذكرة',
    'refunds' => 'تفاصيل الاسترداد',
    'disputes' => 'تفاصيل النزاع',
    _ => 'تفاصيل',
  };
}

IconData _quickActionIcon(String key) {
  return switch (key) {
    'payment_reviews' => Icons.payments_outlined,
    'provider_approvals' => Icons.verified_user_outlined,
    'support_tickets' => Icons.support_agent,
    'refund_requests' => Icons.currency_exchange,
    'disputes' => Icons.report_problem_outlined,
    'audit_log' => Icons.history,
    _ => Icons.chevron_right,
  };
}

IconData _sectionIcon(String section) {
  return switch (section) {
    'payments' => Icons.payments_outlined,
    'providers' => Icons.verified_user_outlined,
    'support/tickets' => Icons.support_agent,
    'refunds' => Icons.currency_exchange,
    'disputes' => Icons.report_problem_outlined,
    _ => Icons.fact_check_outlined,
  };
}

Map<String, Object?> _flatten(Map<String, dynamic> raw, [String prefix = '']) {
  final result = <String, Object?>{};
  raw.forEach((key, value) {
    final nextKey = prefix.isEmpty ? key : '$prefix.$key';
    if (value is Map<String, dynamic>) {
      result.addAll(_flatten(value, nextKey));
    } else if (value is List) {
      result[nextKey] = '${value.length} عنصر';
    } else {
      result[nextKey] = value;
    }
  });
  return result;
}

bool _isUnsafeKey(String key) {
  final lower = key.toLowerCase();
  return lower.contains('path') ||
      lower.contains('secret') ||
      lower.contains('token') ||
      lower.contains('config') ||
      lower.contains('password');
}
