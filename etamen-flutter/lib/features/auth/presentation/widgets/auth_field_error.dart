import 'package:flutter/material.dart';

class AuthFieldError extends StatelessWidget {
  const AuthFieldError({required this.errors, required this.field, super.key});

  final Map<String, List<String>> errors;
  final String field;

  @override
  Widget build(BuildContext context) {
    final messages = errors[field];
    if (messages == null || messages.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Text(
        messages.first,
        style: TextStyle(color: Theme.of(context).colorScheme.error),
      ),
    );
  }
}
