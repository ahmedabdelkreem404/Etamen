import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/config/app_config.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_text_field.dart';
import 'package:etamen_app/features/auth/data/models/login_request.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/auth/presentation/widgets/auth_field_error.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LoginPage extends ConsumerStatefulWidget {
  const LoginPage({super.key});

  @override
  ConsumerState<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends ConsumerState<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final authState = ref.watch(authControllerProvider);

    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 48),
            Text(
              l10n.get('login'),
              style: Theme.of(
                context,
              ).textTheme.headlineMedium?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 24),
            Form(
              key: _formKey,
              child: Column(
                children: [
                  AppTextField(
                    controller: _emailController,
                    label: l10n.get('email'),
                    keyboardType: TextInputType.emailAddress,
                    validator: (value) {
                      if (value == null || value.trim().isEmpty) {
                        return l10n.get('requiredField');
                      }
                      if (!value.contains('@')) return l10n.get('invalidEmail');
                      return null;
                    },
                  ),
                  AuthFieldError(
                    errors: authState.validationErrors,
                    field: 'email',
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    controller: _passwordController,
                    label: l10n.get('password'),
                    obscureText: true,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return l10n.get('requiredField');
                      }
                      return null;
                    },
                  ),
                  AuthFieldError(
                    errors: authState.validationErrors,
                    field: 'password',
                  ),
                  if (authState.error != null) ...[
                    const SizedBox(height: 16),
                    Text(
                      authState.error!,
                      style: TextStyle(
                        color: Theme.of(context).colorScheme.error,
                      ),
                    ),
                  ],
                  const SizedBox(height: 24),
                  AppButton(
                    label: l10n.get('login'),
                    isLoading: authState.isLoading,
                    onPressed: _submit,
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: () => context.go(RouteNames.register),
                    child: Text(l10n.get('register')),
                  ),
                  if (AppConfig.isLocalEnvironment) ...[
                    const SizedBox(height: 16),
                    const Divider(),
                    const SizedBox(height: 8),
                    Text(
                      'دخول سريع محلي للـ QA فقط',
                      style: Theme.of(context).textTheme.labelLarge,
                    ),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      alignment: WrapAlignment.center,
                      children: [
                        OutlinedButton(
                          onPressed: authState.isLoading
                              ? null
                              : () => _loginAs('a@b.co'),
                          child: const Text('Admin QA'),
                        ),
                        OutlinedButton(
                          onPressed: authState.isLoading
                              ? null
                              : () => _loginAs('p@b.co'),
                          child: const Text('Patient QA'),
                        ),
                        OutlinedButton(
                          onPressed: authState.isLoading
                              ? null
                              : () => _loginAs('d@b.co'),
                          child: const Text('Provider QA'),
                        ),
                        OutlinedButton(
                          onPressed: authState.isLoading
                              ? null
                              : () => _loginAs(
                                  'pilot.provider.staff@example.test',
                                ),
                          child: const Text('Staff QA'),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    await ref
        .read(authControllerProvider.notifier)
        .login(
          LoginRequest(
            email: _emailController.text.trim(),
            password: _passwordController.text,
          ),
        );
  }

  Future<void> _loginAs(String email) async {
    _emailController.text = email;
    _passwordController.text = 'Password1234';
    await _submit();
  }
}
