import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/config/app_config.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/support_contact_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

class SupportPage extends StatelessWidget {
  const SupportPage({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final hasEmail = AppConfig.supportEmail.isNotEmpty;
    final hasPhone = AppConfig.supportPhone.isNotEmpty;
    final hasWhatsapp = AppConfig.supportWhatsappUrl.isNotEmpty;

    return AppScaffold(
      title: l10n.get('supportHelp'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          SupportContactCard(
            icon: Icons.email_outlined,
            title: l10n.get('supportEmail'),
            body: hasEmail
                ? AppConfig.supportEmail
                : l10n.get('supportDataComingSoon'),
            actionLabel: hasEmail ? l10n.get('copyEmail') : null,
            onAction: hasEmail
                ? () async {
                    await Clipboard.setData(
                      const ClipboardData(text: AppConfig.supportEmail),
                    );
                    if (!context.mounted) return;
                    ScaffoldMessenger.of(
                      context,
                    ).showSnackBar(SnackBar(content: Text(l10n.get('copied'))));
                  }
                : null,
          ),
          SupportContactCard(
            icon: Icons.alternate_email,
            title: l10n.get('openEmail'),
            body: hasEmail
                ? l10n.get('openEmailHint')
                : l10n.get('supportDataComingSoon'),
            actionLabel: hasEmail ? l10n.get('openEmail') : null,
            onAction: hasEmail
                ? () => launchUrl(
                    Uri(scheme: 'mailto', path: AppConfig.supportEmail),
                  )
                : null,
          ),
          SupportContactCard(
            icon: Icons.phone_outlined,
            title: l10n.get('supportPhone'),
            body: hasPhone
                ? AppConfig.supportPhone
                : l10n.get('supportDataComingSoon'),
          ),
          SupportContactCard(
            icon: Icons.chat_outlined,
            title: l10n.get('supportWhatsapp'),
            body: hasWhatsapp
                ? l10n.get('whatsappConfigured')
                : l10n.get('supportDataComingSoon'),
            actionLabel: hasWhatsapp ? l10n.get('supportWhatsapp') : null,
            onAction: hasWhatsapp
                ? () => launchUrl(Uri.parse(AppConfig.supportWhatsappUrl))
                : null,
          ),
          const SizedBox(height: 12),
          Text(
            l10n.get('supportTopics'),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          ...[
            l10n.get('bookingIssue'),
            l10n.get('paymentIssue'),
            l10n.get('pharmacyOrderIssue'),
            l10n.get('labResultIssue'),
            l10n.get('aiIssue'),
            l10n.get('dataRequestIssue'),
          ].map(
            (topic) => Card(
              child: ListTile(
                leading: const Icon(Icons.help_outline),
                title: Text(topic),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
