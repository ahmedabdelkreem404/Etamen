import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_text_field.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/auth/presentation/widgets/auth_field_error.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class RegisterPage extends ConsumerStatefulWidget {
  const RegisterPage({super.key});

  @override
  ConsumerState<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends ConsumerState<RegisterPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmController = TextEditingController();

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final authState = ref.watch(authControllerProvider);

    return Scaffold(
      appBar: AppBar(),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            Text(
              l10n.get('register'),
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
                    controller: _nameController,
                    label: l10n.get('name'),
                    validator: _required,
                  ),
                  AuthFieldError(
                    errors: authState.validationErrors,
                    field: 'name',
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    controller: _emailController,
                    label: l10n.get('email'),
                    keyboardType: TextInputType.emailAddress,
                    validator: (value) {
                      final required = _required(value);
                      if (required != null) return required;
                      if (!value!.contains('@')) {
                        return l10n.get('invalidEmail');
                      }
                      return null;
                    },
                  ),
                  AuthFieldError(
                    errors: authState.validationErrors,
                    field: 'email',
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    controller: _phoneController,
                    label: l10n.get('phone'),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    controller: _passwordController,
                    label: l10n.get('password'),
                    obscureText: true,
                    validator: (value) {
                      final required = _required(value);
                      if (required != null) return required;
                      if (value!.length < 8) return l10n.get('passwordMin');
                      return null;
                    },
                  ),
                  AuthFieldError(
                    errors: authState.validationErrors,
                    field: 'password',
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    controller: _confirmController,
                    label: l10n.get('confirmPassword'),
                    obscureText: true,
                    validator: (value) {
                      if (value != _passwordController.text) {
                        return l10n.get('passwordMismatch');
                      }
                      return null;
                    },
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
                    label: l10n.get('register'),
                    isLoading: authState.isLoading,
                    onPressed: _submit,
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: () => context.go(RouteNames.login),
                    child: Text(l10n.get('login')),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String? _required(String? value) {
    if (value == null || value.trim().isEmpty) {
      return AppLocalizations.of(context).get('requiredField');
    }
    return null;
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    await ref
        .read(authControllerProvider.notifier)
        .register(
          RegisterRequest(
            name: _nameController.text.trim(),
            email: _emailController.text.trim(),
            phone: _phoneController.text.trim(),
            password: _passwordController.text,
            passwordConfirmation: _confirmController.text,
          ),
        );
  }
}
