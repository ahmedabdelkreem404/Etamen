import 'package:flutter/material.dart';

class LegalSection extends StatelessWidget {
  const LegalSection({required this.text, super.key});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Text(
          text,
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(height: 1.55),
        ),
      ),
    );
  }
}
