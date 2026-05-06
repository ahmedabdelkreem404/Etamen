import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:flutter/widgets.dart';

String notificationCategoryLabel(
  BuildContext context,
  NotificationCategory category,
) {
  final l10n = AppLocalizations.of(context);
  return switch (category) {
    NotificationCategory.appointments => l10n.get('appointmentsCategory'),
    NotificationCategory.payments => l10n.get('paymentsCategory'),
    NotificationCategory.pharmacy => l10n.get('pharmacyCategory'),
    NotificationCategory.labs => l10n.get('labsCategory'),
    NotificationCategory.medications => l10n.get('medicationsCategory'),
    NotificationCategory.carePlans => l10n.get('carePlansCategory'),
    NotificationCategory.wallet => l10n.get('wallet'),
    NotificationCategory.aiSafety => l10n.get('systemCategory'),
    NotificationCategory.system => l10n.get('systemCategory'),
    NotificationCategory.unknown => l10n.get('unknown'),
  };
}

String notificationPriorityLabel(
  BuildContext context,
  NotificationPriority priority,
) {
  final l10n = AppLocalizations.of(context);
  return switch (priority) {
    NotificationPriority.urgent => l10n.get('urgent'),
    NotificationPriority.high => l10n.get('important'),
    NotificationPriority.normal => l10n.get('normalPriority'),
    NotificationPriority.low => l10n.get('lowPriority'),
    NotificationPriority.unknown => l10n.get('unknown'),
  };
}

String notificationChannelLabel(
  BuildContext context,
  NotificationChannel channel,
) {
  final l10n = AppLocalizations.of(context);
  return switch (channel) {
    NotificationChannel.inApp => l10n.get('inApp'),
    NotificationChannel.push => l10n.get('pushNotification'),
    NotificationChannel.email => l10n.get('emailNotification'),
    NotificationChannel.sms => l10n.get('smsNotification'),
    NotificationChannel.whatsapp => l10n.get('whatsappNotification'),
    NotificationChannel.unknown => l10n.get('unknown'),
  };
}
