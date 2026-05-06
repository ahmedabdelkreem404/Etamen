import 'dart:async';

import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/features/auth/data/models/login_request.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_providers.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

class AuthState {
  const AuthState({
    required this.status,
    this.user,
    this.isLoading = false,
    this.error,
    this.validationErrors = const {},
  });

  const AuthState.unknown() : this(status: AuthStatus.unknown);

  final AuthStatus status;
  final AuthUser? user;
  final bool isLoading;
  final String? error;
  final Map<String, List<String>> validationErrors;

  AuthState copyWith({
    AuthStatus? status,
    AuthUser? user,
    bool? isLoading,
    String? error,
    Map<String, List<String>>? validationErrors,
    bool clearError = false,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      validationErrors: validationErrors ?? this.validationErrors,
    );
  }
}

final authControllerProvider = StateNotifierProvider<AuthController, AuthState>(
  (ref) {
    return AuthController(ref);
  },
);

class AuthController extends StateNotifier<AuthState> {
  AuthController(this._ref) : super(const AuthState.unknown());

  final Ref _ref;

  Future<void> restoreSession() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _ref.read(restoreSessionUseCaseProvider).call();
    result.when(
      success: (user) {
        state = AuthState(status: AuthStatus.authenticated, user: user);
        _registerNotificationToken();
      },
      failure: (failure) {
        state = AuthState(
          status: AuthStatus.unauthenticated,
          error: failure.error.message,
        );
      },
    );
  }

  Future<bool> login(LoginRequest request) async {
    state = state.copyWith(
      isLoading: true,
      clearError: true,
      validationErrors: {},
    );
    final result = await _ref.read(loginUseCaseProvider).call(request);
    return result.when(
      success: (session) {
        state = AuthState(status: AuthStatus.authenticated, user: session.user);
        _registerNotificationToken();
        return true;
      },
      failure: (failure) {
        _setFailure(failure.error);
        return false;
      },
    );
  }

  Future<bool> register(RegisterRequest request) async {
    state = state.copyWith(
      isLoading: true,
      clearError: true,
      validationErrors: {},
    );
    final result = await _ref.read(registerUseCaseProvider).call(request);
    return result.when(
      success: (session) {
        state = AuthState(status: AuthStatus.authenticated, user: session.user);
        _registerNotificationToken();
        return true;
      },
      failure: (failure) {
        _setFailure(failure.error);
        return false;
      },
    );
  }

  Future<void> logout() async {
    state = state.copyWith(isLoading: true);
    await _ref
        .read(notificationTokenControllerProvider.notifier)
        .deleteLocalTokens();
    await _ref.read(logoutUseCaseProvider).call();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  void forceLoggedOut() {
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  void _setFailure(ApiError error) {
    state = AuthState(
      status: AuthStatus.unauthenticated,
      error: error.message,
      validationErrors: error.validationErrors,
    );
  }

  void _registerNotificationToken() {
    unawaited(
      _ref
          .read(notificationTokenControllerProvider.notifier)
          .registerLocalToken(),
    );
  }
}
