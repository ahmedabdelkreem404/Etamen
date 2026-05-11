import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/labs/data/datasources/labs_remote_data_source.dart';
import 'package:etamen_app/features/labs/data/models/create_lab_order_request.dart';
import 'package:etamen_app/features/labs/data/repositories/labs_repository_impl.dart';
import 'package:etamen_app/features/labs/domain/entities/lab.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_cart_item.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:etamen_app/features/labs/domain/repositories/labs_repository.dart';
import 'package:etamen_app/features/labs/domain/usecases/create_lab_order.dart';
import 'package:etamen_app/features/labs/domain/usecases/create_lab_order_payment.dart';
import 'package:etamen_app/features/labs/domain/usecases/download_lab_result.dart';
import 'package:etamen_app/features/labs/domain/usecases/get_lab_order_details.dart';
import 'package:etamen_app/features/labs/domain/usecases/get_lab_packages.dart';
import 'package:etamen_app/features/labs/domain/usecases/get_lab_tests.dart';
import 'package:etamen_app/features/labs/domain/usecases/get_labs.dart';
import 'package:etamen_app/features/labs/domain/usecases/get_my_lab_orders.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final labsRemoteDataSourceProvider = Provider<LabsRemoteDataSource>((ref) {
  return LabsRemoteDataSource(ref.watch(apiClientProvider));
});

final labsRepositoryProvider = Provider<LabsRepository>((ref) {
  return LabsRepositoryImpl(ref.watch(labsRemoteDataSourceProvider));
});

class LabsState {
  const LabsState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
  });

  final List<Lab> items;
  final bool isLoading;
  final ApiError? error;
  final String query;

  List<Lab> get filteredItems {
    final needle = query.trim().toLowerCase();
    if (needle.isEmpty) return items;
    return items
        .where(
          (item) =>
              item.name.toLowerCase().contains(needle) ||
              (item.city?.toLowerCase().contains(needle) ?? false) ||
              (item.area?.toLowerCase().contains(needle) ?? false),
        )
        .toList(growable: false);
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  LabsState copyWith({
    List<Lab>? items,
    bool? isLoading,
    ApiError? error,
    String? query,
    bool clearError = false,
  }) {
    return LabsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
    );
  }
}

final labsControllerProvider =
    StateNotifierProvider.autoDispose<LabsController, LabsState>((ref) {
      return LabsController(ref.watch(labsRepositoryProvider))..load();
    });

class LabsController extends StateNotifier<LabsState> {
  LabsController(LabsRepository repository)
    : _getLabs = GetLabs(repository),
      super(const LabsState());

  final GetLabs _getLabs;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getLabs();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void search(String value) => state = state.copyWith(query: value);
}

enum LabCatalogFilter { all, tests, packages, quick }

enum LabCatalogSort { newest, priceAsc, priceDesc, name, resultTime }

class LabTestsState {
  const LabTestsState({
    this.tests = const [],
    this.packages = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
    this.selectedFilter = LabCatalogFilter.all,
    this.selectedSort = LabCatalogSort.newest,
  });

  final List<LabTest> tests;
  final List<LabPackage> packages;
  final bool isLoading;
  final ApiError? error;
  final String query;
  final LabCatalogFilter selectedFilter;
  final LabCatalogSort selectedSort;

  List<LabTest> get filteredTests {
    final needle = query.trim().toLowerCase();
    if (selectedFilter == LabCatalogFilter.packages) return const [];
    final filtered = tests
        .where(
          (item) =>
              (needle.isEmpty ||
                  item.name.toLowerCase().contains(needle) ||
                  (item.description?.toLowerCase().contains(needle) ?? false) ||
                  (item.sampleType?.toLowerCase().contains(needle) ?? false)) &&
              (selectedFilter != LabCatalogFilter.quick ||
                  (item.resultTimeHours ?? 9999) <= 12),
        )
        .toList(growable: false);
    filtered.sort(
      (a, b) => _compareCatalog(
        a.name,
        b.name,
        a.price,
        b.price,
        a.resultTimeHours,
        b.resultTimeHours,
        selectedSort,
      ),
    );
    return filtered;
  }

  List<LabPackage> get filteredPackages {
    final needle = query.trim().toLowerCase();
    if (selectedFilter == LabCatalogFilter.tests) return const [];
    final filtered = packages
        .where(
          (item) =>
              (needle.isEmpty ||
                  item.name.toLowerCase().contains(needle) ||
                  (item.description?.toLowerCase().contains(needle) ?? false) ||
                  item.sampleTypes.any(
                    (sample) => sample.toLowerCase().contains(needle),
                  )) &&
              (selectedFilter != LabCatalogFilter.quick ||
                  (item.resultTimeHours ?? 9999) <= 12),
        )
        .toList(growable: false);
    filtered.sort(
      (a, b) => _compareCatalog(
        a.name,
        b.name,
        a.price,
        b.price,
        a.resultTimeHours,
        b.resultTimeHours,
        selectedSort,
      ),
    );
    return filtered;
  }

  bool get isEmpty =>
      !isLoading &&
      error == null &&
      filteredTests.isEmpty &&
      filteredPackages.isEmpty;

  LabTestsState copyWith({
    List<LabTest>? tests,
    List<LabPackage>? packages,
    bool? isLoading,
    ApiError? error,
    String? query,
    LabCatalogFilter? selectedFilter,
    LabCatalogSort? selectedSort,
    bool clearError = false,
  }) {
    return LabTestsState(
      tests: tests ?? this.tests,
      packages: packages ?? this.packages,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
      selectedFilter: selectedFilter ?? this.selectedFilter,
      selectedSort: selectedSort ?? this.selectedSort,
    );
  }
}

final labTestsControllerProvider = StateNotifierProvider.autoDispose
    .family<LabTestsController, LabTestsState, int>((ref, labId) {
      return LabTestsController(labId, ref.watch(labsRepositoryProvider))
        ..load();
    });

class LabTestsController extends StateNotifier<LabTestsState> {
  LabTestsController(this.labId, LabsRepository repository)
    : _getTests = GetLabTests(repository),
      _getPackages = GetLabPackages(repository),
      super(const LabTestsState());

  final int labId;
  final GetLabTests _getTests;
  final GetLabPackages _getPackages;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final testsResult = await _getTests(labId);
    final packagesResult = await _getPackages(labId);

    final tests = testsResult is ApiSuccess<List<LabTest>>
        ? testsResult.data
        : <LabTest>[];
    final packages = packagesResult is ApiSuccess<List<LabPackage>>
        ? packagesResult.data
        : <LabPackage>[];
    final error = testsResult is ApiFailure<List<LabTest>>
        ? testsResult.error
        : null;

    state = state.copyWith(
      tests: tests,
      packages: packages,
      isLoading: false,
      error: error,
      clearError: error == null,
    );
  }

  void search(String value) => state = state.copyWith(query: value);

  void selectFilter(LabCatalogFilter filter) {
    state = state.copyWith(selectedFilter: filter);
  }

  void selectSort(LabCatalogSort sort) {
    state = state.copyWith(selectedSort: sort);
  }
}

int _compareCatalog(
  String aName,
  String bName,
  String aPrice,
  String bPrice,
  int? aHours,
  int? bHours,
  LabCatalogSort sort,
) {
  return switch (sort) {
    LabCatalogSort.priceAsc => _priceValue(
      aPrice,
    ).compareTo(_priceValue(bPrice)),
    LabCatalogSort.priceDesc => _priceValue(
      bPrice,
    ).compareTo(_priceValue(aPrice)),
    LabCatalogSort.name => aName.compareTo(bName),
    LabCatalogSort.resultTime => (aHours ?? 9999).compareTo(bHours ?? 9999),
    LabCatalogSort.newest => bName.compareTo(aName),
  };
}

double _priceValue(String value) => double.tryParse(value) ?? 0;

class LabCartState {
  const LabCartState({
    this.labId,
    this.items = const [],
    this.sampleCollectionMethod = LabSampleCollectionMethod.branchVisit,
    this.collectionAddress = '',
    this.notes = '',
  });

  final int? labId;
  final List<LabCartItem> items;
  final LabSampleCollectionMethod sampleCollectionMethod;
  final String collectionAddress;
  final String notes;

  bool get isEmpty => items.isEmpty;

  int get itemCount => items.fold(0, (total, item) => total + item.quantity);

  double get localSubtotal =>
      items.fold(0, (total, item) => total + item.localLineTotal);

  bool get requiresHomeAddress =>
      sampleCollectionMethod == LabSampleCollectionMethod.homeCollection;

  bool get hasHomeAddress => collectionAddress.trim().isNotEmpty;

  LabCartState copyWith({
    int? labId,
    List<LabCartItem>? items,
    LabSampleCollectionMethod? sampleCollectionMethod,
    String? collectionAddress,
    String? notes,
  }) {
    return LabCartState(
      labId: labId ?? this.labId,
      items: items ?? this.items,
      sampleCollectionMethod:
          sampleCollectionMethod ?? this.sampleCollectionMethod,
      collectionAddress: collectionAddress ?? this.collectionAddress,
      notes: notes ?? this.notes,
    );
  }
}

final labCartControllerProvider =
    StateNotifierProvider<LabCartController, LabCartState>((ref) {
      return LabCartController();
    });

class LabCartController extends StateNotifier<LabCartState> {
  LabCartController() : super(const LabCartState());

  bool addTest(LabTest test, {required int labId, bool clearExisting = false}) {
    return _add(
      LabCartItem(type: LabCartItemType.test, test: test, quantity: 1),
      labId: labId,
      clearExisting: clearExisting,
    );
  }

  bool addPackage(
    LabPackage package, {
    required int labId,
    bool clearExisting = false,
  }) {
    return _add(
      LabCartItem(type: LabCartItemType.package, package: package, quantity: 1),
      labId: labId,
      clearExisting: clearExisting,
    );
  }

  bool _add(
    LabCartItem newItem, {
    required int labId,
    required bool clearExisting,
  }) {
    if (state.labId != null && state.labId != labId && !clearExisting) {
      return false;
    }
    final items = state.labId == labId && !clearExisting
        ? List<LabCartItem>.of(state.items)
        : <LabCartItem>[];
    final index = items.indexWhere(
      (item) => item.type == newItem.type && item.itemId == newItem.itemId,
    );
    if (index == -1) {
      items.add(newItem);
    } else {
      items[index] = items[index].copyWith(quantity: items[index].quantity + 1);
    }
    state = LabCartState(labId: labId, items: items);
    return true;
  }

  void updateQuantity(LabCartItemType type, int itemId, int quantity) {
    if (quantity <= 0) {
      remove(type, itemId);
      return;
    }
    state = state.copyWith(
      items: state.items
          .map(
            (item) => item.type == type && item.itemId == itemId
                ? item.copyWith(quantity: quantity)
                : item,
          )
          .toList(growable: false),
    );
  }

  void remove(LabCartItemType type, int itemId) {
    final items = state.items
        .where((item) => !(item.type == type && item.itemId == itemId))
        .toList(growable: false);
    state = items.isEmpty ? const LabCartState() : state.copyWith(items: items);
  }

  void setSampleMethod(LabSampleCollectionMethod method) {
    state = state.copyWith(sampleCollectionMethod: method);
  }

  void updateAddress(String value) {
    state = state.copyWith(collectionAddress: value);
  }

  void updateNotes(String value) {
    state = state.copyWith(notes: value);
  }

  void clear() {
    state = const LabCartState();
  }
}

enum LabOrderFilter {
  all,
  review,
  awaitingPayment,
  accepted,
  sampleCollected,
  processing,
  resultReady,
  completed,
  rejected,
}

class LabOrdersState {
  const LabOrdersState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.selectedFilter = LabOrderFilter.all,
  });

  final List<LabOrder> items;
  final bool isLoading;
  final ApiError? error;
  final LabOrderFilter selectedFilter;

  List<LabOrder> get filteredItems {
    return switch (selectedFilter) {
      LabOrderFilter.all => items,
      LabOrderFilter.review =>
        items
            .where((item) => item.status == LabOrderStatus.labReview)
            .toList(growable: false),
      LabOrderFilter.awaitingPayment =>
        items
            .where(
              (item) =>
                  item.status == LabOrderStatus.accepted ||
                  item.status == LabOrderStatus.awaitingPayment ||
                  item.canPay,
            )
            .toList(growable: false),
      LabOrderFilter.accepted =>
        items
            .where(
              (item) =>
                  item.status == LabOrderStatus.accepted ||
                  item.status == LabOrderStatus.paid ||
                  item.status == LabOrderStatus.sampleScheduled,
            )
            .toList(growable: false),
      LabOrderFilter.sampleCollected =>
        items
            .where(
              (item) =>
                  item.status == LabOrderStatus.sampleCollected ||
                  item.status == LabOrderStatus.sampleCollection,
            )
            .toList(growable: false),
      LabOrderFilter.processing =>
        items
            .where((item) => item.status == LabOrderStatus.processing)
            .toList(growable: false),
      LabOrderFilter.resultReady =>
        items
            .where((item) => item.status == LabOrderStatus.resultReady)
            .toList(growable: false),
      LabOrderFilter.completed =>
        items
            .where((item) => item.status == LabOrderStatus.completed)
            .toList(growable: false),
      LabOrderFilter.rejected =>
        items
            .where((item) => item.status.isRejectedOrCancelled)
            .toList(growable: false),
    };
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  LabOrdersState copyWith({
    List<LabOrder>? items,
    bool? isLoading,
    ApiError? error,
    LabOrderFilter? selectedFilter,
    bool clearError = false,
  }) {
    return LabOrdersState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      selectedFilter: selectedFilter ?? this.selectedFilter,
    );
  }
}

final labOrdersControllerProvider =
    StateNotifierProvider.autoDispose<LabOrdersController, LabOrdersState>((
      ref,
    ) {
      return LabOrdersController(ref.watch(labsRepositoryProvider))..load();
    });

class LabOrdersController extends StateNotifier<LabOrdersState> {
  LabOrdersController(LabsRepository repository)
    : _getOrders = GetMyLabOrders(repository),
      super(const LabOrdersState());

  final GetMyLabOrders _getOrders;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getOrders();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void selectFilter(LabOrderFilter filter) {
    state = state.copyWith(selectedFilter: filter);
  }
}

class CreateLabOrderState {
  const CreateLabOrderState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  CreateLabOrderState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return CreateLabOrderState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final createLabOrderControllerProvider =
    StateNotifierProvider.autoDispose<
      CreateLabOrderController,
      CreateLabOrderState
    >((ref) {
      return CreateLabOrderController(ref.watch(labsRepositoryProvider));
    });

class CreateLabOrderController extends StateNotifier<CreateLabOrderState> {
  CreateLabOrderController(LabsRepository repository)
    : _createOrder = CreateLabOrder(repository),
      super(const CreateLabOrderState());

  final CreateLabOrder _createOrder;

  Future<LabOrder?> create(CreateLabOrderRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _createOrder(request);
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

class LabOrderDetailsState {
  const LabOrderDetailsState({
    this.isLoading = false,
    this.isCreatingPayment = false,
    this.isCancelling = false,
    this.order,
    this.paymentStatus,
    this.error,
  });

  final bool isLoading;
  final bool isCreatingPayment;
  final bool isCancelling;
  final LabOrder? order;
  final PaymentStatusDetails? paymentStatus;
  final ApiError? error;

  LabOrderDetailsState copyWith({
    bool? isLoading,
    bool? isCreatingPayment,
    bool? isCancelling,
    LabOrder? order,
    PaymentStatusDetails? paymentStatus,
    ApiError? error,
    bool clearError = false,
  }) {
    return LabOrderDetailsState(
      isLoading: isLoading ?? this.isLoading,
      isCreatingPayment: isCreatingPayment ?? this.isCreatingPayment,
      isCancelling: isCancelling ?? this.isCancelling,
      order: order ?? this.order,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final labOrderDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<LabOrderDetailsController, LabOrderDetailsState, int>((
      ref,
      orderId,
    ) {
      return LabOrderDetailsController(
        orderId,
        ref.watch(labsRepositoryProvider),
        ref.watch(paymentsRepositoryProvider),
      )..load();
    });

class LabOrderDetailsController extends StateNotifier<LabOrderDetailsState> {
  LabOrderDetailsController(
    this.orderId,
    LabsRepository labsRepository,
    PaymentsRepository paymentsRepository,
  ) : _getDetails = GetLabOrderDetails(labsRepository),
      _createPayment = CreateLabOrderPayment(labsRepository),
      _getPaymentStatus = GetPaymentStatus(paymentsRepository),
      _repository = labsRepository,
      super(const LabOrderDetailsState());

  final int orderId;
  final GetLabOrderDetails _getDetails;
  final CreateLabOrderPayment _createPayment;
  final GetPaymentStatus _getPaymentStatus;
  final LabsRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getDetails(orderId);
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

  Future<int?> createPayment() async {
    state = state.copyWith(isCreatingPayment: true, clearError: true);
    final result = await _createPayment(orderId);
    return result.when(
      success: (payment) {
        state = state.copyWith(
          isCreatingPayment: false,
          order: payment.order,
          clearError: true,
        );
        final paymentId = payment.paymentId ?? payment.order.paymentId;
        if (paymentId != null) _loadPaymentStatus(paymentId);
        return paymentId;
      },
      failure: (failure) {
        state = state.copyWith(isCreatingPayment: false, error: failure.error);
        return null;
      },
    );
  }

  Future<bool> cancel({String? reason}) async {
    state = state.copyWith(isCancelling: true, clearError: true);
    final result = await _repository.cancelOrder(orderId, reason: reason);
    return result.when(
      success: (order) {
        state = state.copyWith(
          isCancelling: false,
          order: order,
          paymentStatus: null,
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isCancelling: false, error: failure.error);
        return false;
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

class LabResultDownloadState {
  const LabResultDownloadState({
    this.isDownloading = false,
    this.download,
    this.error,
  });

  final bool isDownloading;
  final LabResultDownload? download;
  final ApiError? error;

  LabResultDownloadState copyWith({
    bool? isDownloading,
    LabResultDownload? download,
    ApiError? error,
    bool clearError = false,
  }) {
    return LabResultDownloadState(
      isDownloading: isDownloading ?? this.isDownloading,
      download: download ?? this.download,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final labResultDownloadControllerProvider =
    StateNotifierProvider.autoDispose<
      LabResultDownloadController,
      LabResultDownloadState
    >((ref) {
      return LabResultDownloadController(ref.watch(labsRepositoryProvider));
    });

class LabResultDownloadController
    extends StateNotifier<LabResultDownloadState> {
  LabResultDownloadController(LabsRepository repository)
    : _download = DownloadLabResult(repository),
      super(const LabResultDownloadState());

  final DownloadLabResult _download;

  Future<LabResultDownload?> download(int resultId) async {
    state = state.copyWith(isDownloading: true, clearError: true);
    final result = await _download(resultId);
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
