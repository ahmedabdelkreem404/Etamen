import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/notifications/local_notification_token_provider.dart';
import 'package:etamen_app/core/notifications/notification_token_provider.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/notifications/data/datasources/notifications_remote_data_source.dart';
import 'package:etamen_app/features/notifications/data/models/update_notification_preferences_request.dart';
import 'package:etamen_app/features/notifications/data/repositories/notifications_repository_impl.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_token.dart';
import 'package:etamen_app/features/notifications/domain/repositories/notifications_repository.dart';
import 'package:etamen_app/features/notifications/domain/usecases/delete_notification.dart';
import 'package:etamen_app/features/notifications/domain/usecases/delete_notification_token.dart';
import 'package:etamen_app/features/notifications/domain/usecases/get_notification_details.dart';
import 'package:etamen_app/features/notifications/domain/usecases/get_notification_preferences.dart';
import 'package:etamen_app/features/notifications/domain/usecases/get_notification_tokens.dart';
import 'package:etamen_app/features/notifications/domain/usecases/get_notifications.dart';
import 'package:etamen_app/features/notifications/domain/usecases/get_unread_count.dart';
import 'package:etamen_app/features/notifications/domain/usecases/mark_all_notifications_read.dart';
import 'package:etamen_app/features/notifications/domain/usecases/mark_notification_read.dart';
import 'package:etamen_app/features/notifications/domain/usecases/register_notification_token.dart';
import 'package:etamen_app/features/notifications/domain/usecases/update_notification_preferences.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final notificationsRemoteDataSourceProvider =
    Provider<NotificationsRemoteDataSource>((ref) {
      return NotificationsRemoteDataSource(ref.watch(apiClientProvider));
    });

final notificationsRepositoryProvider = Provider<NotificationsRepository>((
  ref,
) {
  return NotificationsRepositoryImpl(
    ref.watch(notificationsRemoteDataSourceProvider),
  );
});

final notificationTokenProviderSourceProvider =
    Provider<NotificationTokenProviderSource>((ref) {
      return LocalNotificationTokenProvider();
    });

enum NotificationFilter {
  all,
  unread,
  appointments,
  payments,
  pharmacy,
  labs,
  medications,
  carePlans,
  system,
}

class NotificationsState {
  const NotificationsState({
    this.items = const [],
    this.isLoading = false,
    this.isSubmitting = false,
    this.error,
    this.filter = NotificationFilter.all,
  });

  final List<AppNotification> items;
  final bool isLoading;
  final bool isSubmitting;
  final ApiError? error;
  final NotificationFilter filter;

  List<AppNotification> get filteredItems {
    return switch (filter) {
      NotificationFilter.all => items,
      NotificationFilter.unread =>
        items.where((item) => !item.isRead).toList(growable: false),
      NotificationFilter.appointments => _byCategory(
        NotificationCategory.appointments,
      ),
      NotificationFilter.payments => _byCategory(NotificationCategory.payments),
      NotificationFilter.pharmacy => _byCategory(NotificationCategory.pharmacy),
      NotificationFilter.labs => _byCategory(NotificationCategory.labs),
      NotificationFilter.medications => _byCategory(
        NotificationCategory.medications,
      ),
      NotificationFilter.carePlans => _byCategory(
        NotificationCategory.carePlans,
      ),
      NotificationFilter.system => _byCategory(NotificationCategory.system),
    };
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  List<AppNotification> _byCategory(NotificationCategory category) {
    return items
        .where((item) => item.category == category)
        .toList(growable: false);
  }

  NotificationsState copyWith({
    List<AppNotification>? items,
    bool? isLoading,
    bool? isSubmitting,
    ApiError? error,
    NotificationFilter? filter,
    bool clearError = false,
  }) {
    return NotificationsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
      filter: filter ?? this.filter,
    );
  }
}

final notificationsControllerProvider =
    StateNotifierProvider.autoDispose<
      NotificationsController,
      NotificationsState
    >((ref) {
      return NotificationsController(
        ref.watch(notificationsRepositoryProvider),
        onBadgeChanged: () =>
            ref.read(notificationBadgeControllerProvider.notifier).load(),
      )..load();
    });

class NotificationsController extends StateNotifier<NotificationsState> {
  NotificationsController(
    NotificationsRepository repository, {
    Future<void> Function()? onBadgeChanged,
  }) : _getNotifications = GetNotifications(repository),
       _markRead = MarkNotificationRead(repository),
       _markAllRead = MarkAllNotificationsRead(repository),
       _deleteNotification = DeleteNotification(repository),
       _onBadgeChanged = onBadgeChanged,
       super(const NotificationsState());

  final GetNotifications _getNotifications;
  final MarkNotificationRead _markRead;
  final MarkAllNotificationsRead _markAllRead;
  final DeleteNotification _deleteNotification;
  final Future<void> Function()? _onBadgeChanged;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getNotifications();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void selectFilter(NotificationFilter filter) {
    state = state.copyWith(filter: filter);
  }

  Future<bool> markRead(int id) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _markRead(id);
    return result.when(
      success: (notification) async {
        state = state.copyWith(
          isSubmitting: false,
          items: state.items
              .map((item) => item.id == id ? notification : item)
              .toList(growable: false),
          clearError: true,
        );
        await _onBadgeChanged?.call();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }

  Future<bool> markAllRead() async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _markAllRead();
    return result.when(
      success: (_) async {
        await load();
        await _onBadgeChanged?.call();
        state = state.copyWith(isSubmitting: false, clearError: true);
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }

  Future<bool> delete(int id) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _deleteNotification(id);
    return result.when(
      success: (_) async {
        state = state.copyWith(
          isSubmitting: false,
          items: state.items.where((item) => item.id != id).toList(),
          clearError: true,
        );
        await _onBadgeChanged?.call();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class NotificationBadgeState {
  const NotificationBadgeState({
    this.unreadCount = 0,
    this.isLoading = false,
    this.error,
  });

  final int unreadCount;
  final bool isLoading;
  final ApiError? error;

  NotificationBadgeState copyWith({
    int? unreadCount,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return NotificationBadgeState(
      unreadCount: unreadCount ?? this.unreadCount,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final notificationBadgeControllerProvider =
    StateNotifierProvider<NotificationBadgeController, NotificationBadgeState>((
      ref,
    ) {
      return NotificationBadgeController(
        ref.watch(notificationsRepositoryProvider),
      )..load();
    });

class NotificationBadgeController
    extends StateNotifier<NotificationBadgeState> {
  NotificationBadgeController(NotificationsRepository repository)
    : _getUnreadCount = GetUnreadCount(repository),
      super(const NotificationBadgeState());

  final GetUnreadCount _getUnreadCount;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getUnreadCount();
    state = result.when(
      success: (count) => state.copyWith(
        unreadCount: count.unreadCount,
        isLoading: false,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

class NotificationDetailsState {
  const NotificationDetailsState({
    this.isLoading = false,
    this.isSubmitting = false,
    this.notification,
    this.error,
  });

  final bool isLoading;
  final bool isSubmitting;
  final AppNotification? notification;
  final ApiError? error;

  NotificationDetailsState copyWith({
    bool? isLoading,
    bool? isSubmitting,
    AppNotification? notification,
    ApiError? error,
    bool clearError = false,
  }) {
    return NotificationDetailsState(
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      notification: notification ?? this.notification,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final notificationDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<NotificationDetailsController, NotificationDetailsState, int>((
      ref,
      id,
    ) {
      return NotificationDetailsController(
        id,
        ref.watch(notificationsRepositoryProvider),
        onBadgeChanged: () =>
            ref.read(notificationBadgeControllerProvider.notifier).load(),
      )..load();
    });

class NotificationDetailsController
    extends StateNotifier<NotificationDetailsState> {
  NotificationDetailsController(
    this.id,
    NotificationsRepository repository, {
    Future<void> Function()? onBadgeChanged,
  }) : _getDetails = GetNotificationDetails(repository),
       _markRead = MarkNotificationRead(repository),
       _deleteNotification = DeleteNotification(repository),
       _onBadgeChanged = onBadgeChanged,
       super(const NotificationDetailsState());

  final int id;
  final GetNotificationDetails _getDetails;
  final MarkNotificationRead _markRead;
  final DeleteNotification _deleteNotification;
  final Future<void> Function()? _onBadgeChanged;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getDetails(id);
    state = result.when(
      success: (notification) => state.copyWith(
        isLoading: false,
        notification: notification,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<bool> markRead() async {
    final notification = state.notification;
    if (notification?.isRead == true) return true;
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _markRead(id);
    return result.when(
      success: (updated) async {
        state = state.copyWith(
          isSubmitting: false,
          notification: updated,
          clearError: true,
        );
        await _onBadgeChanged?.call();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }

  Future<bool> delete() async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _deleteNotification(id);
    return result.when(
      success: (_) async {
        state = state.copyWith(isSubmitting: false, clearError: true);
        await _onBadgeChanged?.call();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class NotificationPreferencesState {
  const NotificationPreferencesState({
    this.preferences = const [],
    this.isLoading = false,
    this.isSubmitting = false,
    this.error,
  });

  final List<NotificationPreference> preferences;
  final bool isLoading;
  final bool isSubmitting;
  final ApiError? error;

  NotificationPreferencesState copyWith({
    List<NotificationPreference>? preferences,
    bool? isLoading,
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return NotificationPreferencesState(
      preferences: preferences ?? this.preferences,
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final notificationPreferencesControllerProvider =
    StateNotifierProvider.autoDispose<
      NotificationPreferencesController,
      NotificationPreferencesState
    >((ref) {
      return NotificationPreferencesController(
        ref.watch(notificationsRepositoryProvider),
      )..load();
    });

class NotificationPreferencesController
    extends StateNotifier<NotificationPreferencesState> {
  NotificationPreferencesController(NotificationsRepository repository)
    : _getPreferences = GetNotificationPreferences(repository),
      _updatePreferences = UpdateNotificationPreferences(repository),
      super(const NotificationPreferencesState());

  final GetNotificationPreferences _getPreferences;
  final UpdateNotificationPreferences _updatePreferences;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getPreferences();
    state = result.when(
      success: (preferences) => state.copyWith(
        preferences: preferences,
        isLoading: false,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void toggle(NotificationPreference preference, bool enabled) {
    state = state.copyWith(
      preferences: state.preferences
          .map((item) {
            final isSame =
                item.channel == preference.channel &&
                item.category == preference.category;
            return isSame ? item.copyWith(isEnabled: enabled) : item;
          })
          .toList(growable: false),
    );
  }

  Future<bool> save() async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _updatePreferences(
      UpdateNotificationPreferencesRequest(preferences: state.preferences),
    );
    return result.when(
      success: (preferences) {
        state = state.copyWith(
          preferences: preferences,
          isSubmitting: false,
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class NotificationTokenState {
  const NotificationTokenState({
    this.tokens = const [],
    this.isSubmitting = false,
    this.error,
  });

  final List<NotificationToken> tokens;
  final bool isSubmitting;
  final ApiError? error;

  NotificationTokenState copyWith({
    List<NotificationToken>? tokens,
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return NotificationTokenState(
      tokens: tokens ?? this.tokens,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final notificationTokenControllerProvider =
    StateNotifierProvider<NotificationTokenController, NotificationTokenState>((
      ref,
    ) {
      return NotificationTokenController(
        ref.watch(notificationsRepositoryProvider),
        ref.watch(notificationTokenProviderSourceProvider),
      );
    });

class NotificationTokenController
    extends StateNotifier<NotificationTokenState> {
  NotificationTokenController(
    NotificationsRepository repository,
    this._tokenProvider,
  ) : _getTokens = GetNotificationTokens(repository),
      _registerToken = RegisterNotificationToken(repository),
      _deleteToken = DeleteNotificationToken(repository),
      super(const NotificationTokenState());

  final NotificationTokenProviderSource _tokenProvider;
  final GetNotificationTokens _getTokens;
  final RegisterNotificationToken _registerToken;
  final DeleteNotificationToken _deleteToken;

  Future<bool> registerLocalToken() async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final request = await _tokenProvider.getTokenRequest(locale: 'ar');
    final result = await _registerToken(request);
    return result.when(
      success: (token) {
        state = state.copyWith(
          isSubmitting: false,
          tokens: [token, ...state.tokens.where((item) => item.id != token.id)],
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }

  Future<bool> deleteLocalTokens() async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final tokensResult = await _getTokens();
    return tokensResult.when(
      success: (tokens) async {
        final localTokens = tokens.where(
          (token) => token.provider == NotificationTokenProvider.local,
        );
        var hasFailure = false;
        for (final token in localTokens) {
          final result = await _deleteToken(token.id);
          result.when(success: (_) {}, failure: (_) => hasFailure = true);
        }
        state = state.copyWith(
          isSubmitting: false,
          tokens: tokens
              .where(
                (token) => token.provider != NotificationTokenProvider.local,
              )
              .toList(growable: false),
          clearError: true,
        );
        return !hasFailure;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}
