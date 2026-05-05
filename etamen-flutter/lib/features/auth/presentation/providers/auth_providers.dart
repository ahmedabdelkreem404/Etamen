import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:etamen_app/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:etamen_app/features/auth/domain/repositories/auth_repository.dart';
import 'package:etamen_app/features/auth/domain/usecases/login_use_case.dart';
import 'package:etamen_app/features/auth/domain/usecases/logout_use_case.dart';
import 'package:etamen_app/features/auth/domain/usecases/register_use_case.dart';
import 'package:etamen_app/features/auth/domain/usecases/restore_session_use_case.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final authRemoteDataSourceProvider = Provider<AuthRemoteDataSource>((ref) {
  return AuthRemoteDataSource(ref.watch(apiClientProvider));
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepositoryImpl(
    remoteDataSource: ref.watch(authRemoteDataSourceProvider),
    tokenStorage: ref.watch(tokenStorageProvider),
  );
});

final loginUseCaseProvider = Provider<LoginUseCase>((ref) {
  return LoginUseCase(ref.watch(authRepositoryProvider));
});

final registerUseCaseProvider = Provider<RegisterUseCase>((ref) {
  return RegisterUseCase(ref.watch(authRepositoryProvider));
});

final restoreSessionUseCaseProvider = Provider<RestoreSessionUseCase>((ref) {
  return RestoreSessionUseCase(ref.watch(authRepositoryProvider));
});

final logoutUseCaseProvider = Provider<LogoutUseCase>((ref) {
  return LogoutUseCase(ref.watch(authRepositoryProvider));
});
