import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:etamen_app/features/radiology/data/datasources/radiology_remote_data_source.dart';
import 'package:etamen_app/features/radiology/data/models/create_radiology_order_request.dart';
import 'package:etamen_app/features/radiology/data/repositories/radiology_repository_impl.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_cart_item.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_scan_category.dart';
import 'package:etamen_app/features/radiology/domain/repositories/radiology_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final radiologyRemoteDataSourceProvider = Provider<RadiologyRemoteDataSource>((
  ref,
) {
  return RadiologyRemoteDataSource(ref.watch(apiClientProvider));
});

final radiologyRepositoryProvider = Provider<RadiologyRepository>((ref) {
  return RadiologyRepositoryImpl(ref.watch(radiologyRemoteDataSourceProvider));
});

class RadiologyCatalogState {
  const RadiologyCatalogState({
    this.categories = const [],
    this.scans = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
    this.selectedCategoryId,
  });

  final List<RadiologyScanCategory> categories;
  final List<RadiologyScan> scans;
  final bool isLoading;
  final ApiError? error;
  final String query;
  final int? selectedCategoryId;

  List<RadiologyScan> get filteredScans {
    var result = scans;
    if (selectedCategoryId != null) {
      result = result
          .where((item) => item.categoryId == selectedCategoryId)
          .toList(growable: false);
    }
    final needle = query.trim().toLowerCase();
    if (needle.isEmpty) return result;
    return result
        .where((item) {
          final provider = item.provider;
          return item.nameAr.toLowerCase().contains(needle) ||
              (item.nameEn?.toLowerCase().contains(needle) ?? false) ||
              (item.category?.nameAr.toLowerCase().contains(needle) ?? false) ||
              (provider?.nameAr.toLowerCase().contains(needle) ?? false) ||
              (provider?.nameEn?.toLowerCase().contains(needle) ?? false);
        })
        .toList(growable: false);
  }

  bool get isEmpty => !isLoading && error == null && filteredScans.isEmpty;

  RadiologyCatalogState copyWith({
    List<RadiologyScanCategory>? categories,
    List<RadiologyScan>? scans,
    bool? isLoading,
    ApiError? error,
    String? query,
    int? selectedCategoryId,
    bool clearError = false,
    bool clearCategory = false,
  }) {
    return RadiologyCatalogState(
      categories: categories ?? this.categories,
      scans: scans ?? this.scans,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
      selectedCategoryId: clearCategory
          ? null
          : selectedCategoryId ?? this.selectedCategoryId,
    );
  }
}

final radiologyCatalogControllerProvider =
    StateNotifierProvider.autoDispose<
      RadiologyCatalogController,
      RadiologyCatalogState
    >((ref) {
      return RadiologyCatalogController(ref.watch(radiologyRepositoryProvider))
        ..load();
    });

class RadiologyCatalogController extends StateNotifier<RadiologyCatalogState> {
  RadiologyCatalogController(this._repository)
    : super(const RadiologyCatalogState());

  final RadiologyRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final categoriesResult = await _repository.getCategories();
    final scansResult = await _repository.getScans();

    final categories =
        categoriesResult is ApiSuccess<List<RadiologyScanCategory>>
        ? categoriesResult.data
        : <RadiologyScanCategory>[];
    final scans = scansResult is ApiSuccess<List<RadiologyScan>>
        ? scansResult.data
        : <RadiologyScan>[];
    final error = scansResult is ApiFailure<List<RadiologyScan>>
        ? scansResult.error
        : categoriesResult is ApiFailure<List<RadiologyScanCategory>>
        ? categoriesResult.error
        : null;

    state = state.copyWith(
      categories: categories,
      scans: scans,
      isLoading: false,
      error: error,
      clearError: error == null,
    );
  }

  void search(String value) => state = state.copyWith(query: value);

  void selectCategory(int? categoryId) {
    if (categoryId == null) {
      state = state.copyWith(clearCategory: true);
      return;
    }
    state = state.copyWith(selectedCategoryId: categoryId);
  }
}

class RadiologyCartState {
  const RadiologyCartState({
    this.providerId,
    this.branchId,
    this.items = const [],
    this.notes = '',
    this.lastMessage,
  });

  final int? providerId;
  final int? branchId;
  final List<RadiologyCartItem> items;
  final String notes;
  final String? lastMessage;

  bool get isEmpty => items.isEmpty;

  int get itemCount => items.fold(0, (total, item) => total + item.quantity);

  double get localTotal =>
      items.fold(0, (total, item) => total + item.localLineTotal);

  RadiologyCartState copyWith({
    int? providerId,
    int? branchId,
    List<RadiologyCartItem>? items,
    String? notes,
    String? lastMessage,
    bool clearProvider = false,
    bool clearBranch = false,
    bool clearMessage = false,
  }) {
    return RadiologyCartState(
      providerId: clearProvider ? null : providerId ?? this.providerId,
      branchId: clearBranch ? null : branchId ?? this.branchId,
      items: items ?? this.items,
      notes: notes ?? this.notes,
      lastMessage: clearMessage ? null : lastMessage ?? this.lastMessage,
    );
  }
}

final radiologyCartControllerProvider =
    StateNotifierProvider<RadiologyCartController, RadiologyCartState>((ref) {
      return RadiologyCartController();
    });

class RadiologyCartController extends StateNotifier<RadiologyCartState> {
  RadiologyCartController() : super(const RadiologyCartState());

  bool addScan(RadiologyScan scan) {
    if (state.providerId != null && state.providerId != scan.providerId) {
      state = state.copyWith(
        lastMessage: 'اختر فحوصات من نفس مركز الأشعة في الطلب الواحد.',
      );
      return false;
    }

    var branchId = state.branchId;
    if (branchId != null &&
        scan.branchId != null &&
        branchId != scan.branchId) {
      state = state.copyWith(
        lastMessage: 'اختر فحوصات من نفس الفرع في الطلب الواحد.',
      );
      return false;
    }
    branchId ??= scan.branchId;

    final items = List<RadiologyCartItem>.of(state.items);
    final index = items.indexWhere((item) => item.scanId == scan.id);
    if (index == -1) {
      items.add(RadiologyCartItem(scan: scan));
    } else {
      items[index] = items[index].copyWith(quantity: items[index].quantity + 1);
    }

    state = RadiologyCartState(
      providerId: scan.providerId,
      branchId: branchId,
      items: items,
    );
    return true;
  }

  void updateQuantity(int scanId, int quantity) {
    if (quantity <= 0) {
      remove(scanId);
      return;
    }
    state = state.copyWith(
      items: state.items
          .map(
            (item) => item.scanId == scanId
                ? item.copyWith(quantity: quantity)
                : item,
          )
          .toList(growable: false),
      clearMessage: true,
    );
  }

  void remove(int scanId) {
    final items = state.items
        .where((item) => item.scanId != scanId)
        .toList(growable: false);
    state = items.isEmpty
        ? const RadiologyCartState()
        : state.copyWith(items: items, clearMessage: true);
  }

  void updateNotes(String value) {
    state = state.copyWith(notes: value);
  }

  void clear() {
    state = const RadiologyCartState();
  }
}

class CreateRadiologyOrderState {
  const CreateRadiologyOrderState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  CreateRadiologyOrderState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return CreateRadiologyOrderState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final createRadiologyOrderControllerProvider =
    StateNotifierProvider.autoDispose<
      CreateRadiologyOrderController,
      CreateRadiologyOrderState
    >((ref) {
      return CreateRadiologyOrderController(
        ref.watch(radiologyRepositoryProvider),
      );
    });

class CreateRadiologyOrderController
    extends StateNotifier<CreateRadiologyOrderState> {
  CreateRadiologyOrderController(this._repository)
    : super(const CreateRadiologyOrderState());

  final RadiologyRepository _repository;

  Future<RadiologyOrder?> create(CreateRadiologyOrderRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _repository.createOrder(request);
    return result.when(
      success: (order) {
        state = state.copyWith(isSubmitting: false, clearError: true);
        return order;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }
}

class RadiologyOrdersState {
  const RadiologyOrdersState({
    this.items = const [],
    this.isLoading = false,
    this.error,
  });

  final List<RadiologyOrder> items;
  final bool isLoading;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  RadiologyOrdersState copyWith({
    List<RadiologyOrder>? items,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return RadiologyOrdersState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final radiologyOrdersControllerProvider =
    StateNotifierProvider.autoDispose<
      RadiologyOrdersController,
      RadiologyOrdersState
    >((ref) {
      return RadiologyOrdersController(ref.watch(radiologyRepositoryProvider))
        ..load();
    });

class RadiologyOrdersController extends StateNotifier<RadiologyOrdersState> {
  RadiologyOrdersController(this._repository)
    : super(const RadiologyOrdersState());

  final RadiologyRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getMyOrders();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

class RadiologyOrderDetailsState {
  const RadiologyOrderDetailsState({
    this.isLoading = false,
    this.order,
    this.paymentStatus,
    this.error,
  });

  final bool isLoading;
  final RadiologyOrder? order;
  final PaymentStatusDetails? paymentStatus;
  final ApiError? error;

  RadiologyOrderDetailsState copyWith({
    bool? isLoading,
    RadiologyOrder? order,
    PaymentStatusDetails? paymentStatus,
    ApiError? error,
    bool clearError = false,
  }) {
    return RadiologyOrderDetailsState(
      isLoading: isLoading ?? this.isLoading,
      order: order ?? this.order,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final radiologyOrderDetailsControllerProvider = StateNotifierProvider
    .autoDispose
    .family<RadiologyOrderDetailsController, RadiologyOrderDetailsState, int>((
      ref,
      orderId,
    ) {
      return RadiologyOrderDetailsController(
        orderId,
        ref.watch(radiologyRepositoryProvider),
        ref.watch(paymentsRepositoryProvider),
      )..load();
    });

class RadiologyOrderDetailsController
    extends StateNotifier<RadiologyOrderDetailsState> {
  RadiologyOrderDetailsController(
    this.orderId,
    RadiologyRepository radiologyRepository,
    PaymentsRepository paymentsRepository,
  ) : _radiologyRepository = radiologyRepository,
      _getPaymentStatus = GetPaymentStatus(paymentsRepository),
      super(const RadiologyOrderDetailsState());

  final int orderId;
  final RadiologyRepository _radiologyRepository;
  final GetPaymentStatus _getPaymentStatus;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _radiologyRepository.getOrderDetails(orderId);
    await result.when(
      success: (order) async {
        state = state.copyWith(
          order: order,
          isLoading: false,
          clearError: true,
        );
        await _loadPaymentStatus(order.paymentId);
      },
      failure: (failure) async {
        state = state.copyWith(isLoading: false, error: failure.error);
      },
    );
  }

  Future<void> _loadPaymentStatus(int? paymentId) async {
    if (paymentId == null) return;
    final result = await _getPaymentStatus(paymentId);
    state = result.when(
      success: (status) => state.copyWith(paymentStatus: status),
      failure: (failure) => state.copyWith(error: failure.error),
    );
  }
}

class RadiologyResultDownloadState {
  const RadiologyResultDownloadState({
    this.isDownloading = false,
    this.download,
    this.error,
  });

  final bool isDownloading;
  final RadiologyResultDownload? download;
  final ApiError? error;

  RadiologyResultDownloadState copyWith({
    bool? isDownloading,
    RadiologyResultDownload? download,
    ApiError? error,
    bool clearError = false,
  }) {
    return RadiologyResultDownloadState(
      isDownloading: isDownloading ?? this.isDownloading,
      download: download ?? this.download,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final radiologyResultDownloadControllerProvider =
    StateNotifierProvider.autoDispose<
      RadiologyResultDownloadController,
      RadiologyResultDownloadState
    >((ref) {
      return RadiologyResultDownloadController(
        ref.watch(radiologyRepositoryProvider),
      );
    });

class RadiologyResultDownloadController
    extends StateNotifier<RadiologyResultDownloadState> {
  RadiologyResultDownloadController(this._repository)
    : super(const RadiologyResultDownloadState());

  final RadiologyRepository _repository;

  Future<RadiologyResultDownload?> download(int resultId) async {
    state = state.copyWith(isDownloading: true, clearError: true);
    final result = await _repository.downloadResult(resultId);
    return result.when(
      success: (download) {
        state = state.copyWith(
          isDownloading: false,
          download: download,
          clearError: true,
        );
        return download;
      },
      failure: (failure) {
        state = state.copyWith(isDownloading: false, error: failure.error);
        return null;
      },
    );
  }
}
