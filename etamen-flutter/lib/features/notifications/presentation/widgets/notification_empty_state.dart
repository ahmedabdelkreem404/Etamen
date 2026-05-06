import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:flutter/material.dart';

class NotificationEmptyState extends StatelessWidget {
  const NotificationEmptyState({required this.message, super.key});

  final String message;

  @override
  Widget build(BuildContext context) {
    return EmptyView(message: message, icon: Icons.notifications_none);
  }
}
