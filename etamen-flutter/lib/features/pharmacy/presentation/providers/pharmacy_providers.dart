import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:etamen_app/features/pharmacy/data/datasources/pharmacy_remote_data_source.dart';
import 'package:etamen_app/features/pharmacy/data/models/create_pharmacy_order_request.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/data/repositories/pharmacy_repository_impl.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_cart_item.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';
import 'package:etamen_app/features/pharmacy/domain/repositories/pharmacy_repository.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/create_pharmacy_order.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/create_pharmacy_order_payment.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/get_my_pharmacy_orders.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/get_pharmacies.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/get_pharmacy_order_details.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/get_pharmacy_products.dart';
import 'package:etamen_app/features/pharmacy/domain/usecases/upload_prescription.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final pharmacyRemoteDataSourceProvider = Provider<PharmacyRemoteDataSource>((
  ref,
) {
  return PharmacyRemoteDataSource(ref.watch(apiClientProvider));
});

final pharmacyRepositoryProvider = Provider<PharmacyRepository>((ref) {
  return PharmacyRepositoryImpl(ref.watch(pharmacyRemoteDataSourceProvider));
});

class PharmaciesState {
  const PharmaciesState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
  });

  final List<Pharmacy> items;
  final bool isLoading;
  final ApiError? error;
  final String query;

  List<Pharmacy> get filteredItems {
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

  PharmaciesState copyWith({
    List<Pharmacy>? items,
    bool? isLoading,
    ApiError? error,
    String? query,
    bool clearError = false,
  }) {
    return PharmaciesState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
    );
  }
}

final pharmaciesControllerProvider =
    StateNotifierProvider.autoDispose<PharmaciesController, PharmaciesState>((
      ref,
    ) {
      return PharmaciesController(ref.watch(pharmacyRepositoryProvider))
        ..load();
    });

class PharmaciesController extends StateNotifier<PharmaciesState> {
  PharmaciesController(PharmacyRepository repository)
    : _getPharmacies = GetPharmacies(repository),
      super(const PharmaciesState());

  final GetPharmacies _getPharmacies;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getPharmacies();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void search(String value) {
    state = state.copyWith(query: value);
  }
}

class PharmacyProductsState {
  const PharmacyProductsState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
  });

  final List<PharmacyProduct> items;
  final bool isLoading;
  final ApiError? error;
  final String query;

  List<PharmacyProduct> get filteredItems {
    final needle = query.trim().toLowerCase();
    if (needle.isEmpty) return items;
    return items
        .where(
          (item) =>
              item.name.toLowerCase().contains(needle) ||
              (item.description?.toLowerCase().contains(needle) ?? false) ||
              (item.category?.toLowerCase().contains(needle) ?? false),
        )
        .toList(growable: false);
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  PharmacyProductsState copyWith({
    List<PharmacyProduct>? items,
    bool? isLoading,
    ApiError? error,
    String? query,
    bool clearError = false,
  }) {
    return PharmacyProductsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
    );
  }
}

final pharmacyProductsControllerProvider = StateNotifierProvider.autoDispose
    .family<PharmacyProductsController, PharmacyProductsState, int>((
      ref,
      pharmacyId,
    ) {
      return PharmacyProductsController(
        pharmacyId,
        ref.watch(pharmacyRepositoryProvider),
      )..load();
    });

class PharmacyProductsController extends StateNotifier<PharmacyProductsState> {
  PharmacyProductsController(this.pharmacyId, PharmacyRepository repository)
    : _getProducts = GetPharmacyProducts(repository),
      super(const PharmacyProductsState());

  final int pharmacyId;
  final GetPharmacyProducts _getProducts;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getProducts(pharmacyId);
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void search(String value) {
    state = state.copyWith(query: value);
  }
}

class PharmacyCartState {
  const PharmacyCartState({
    this.pharmacyId,
    this.items = const [],
    this.prescription,
    this.notes = '',
    this.deliveryAddress = '',
  });

  final int? pharmacyId;
  final List<PharmacyCartItem> items;
  final PharmacyPrescription? prescription;
  final String notes;
  final String deliveryAddress;

  bool get isEmpty => items.isEmpty;

  bool get requiresPrescription =>
      items.any((item) => item.product.requiresPrescription);

  int get itemCount => items.fold(0, (total, item) => total + item.quantity);

  double get localSubtotal =>
      items.fold(0, (total, item) => total + item.localLineTotal);

  PharmacyCartState copyWith({
    int? pharmacyId,
    List<PharmacyCartItem>? items,
    PharmacyPrescription? prescription,
    String? notes,
    String? deliveryAddress,
    bool clearPrescription = false,
  }) {
    return PharmacyCartState(
      pharmacyId: pharmacyId ?? this.pharmacyId,
      items: items ?? this.items,
      prescription: clearPrescription
          ? null
          : prescription ?? this.prescription,
      notes: notes ?? this.notes,
      deliveryAddress: deliveryAddress ?? this.deliveryAddress,
    );
  }
}

final pharmacyCartControllerProvider =
    StateNotifierProvider<PharmacyCartController, PharmacyCartState>((ref) {
      return PharmacyCartController();
    });

class PharmacyCartController extends StateNotifier<PharmacyCartState> {
  PharmacyCartController() : super(const PharmacyCartState());

  bool addProduct(
    PharmacyProduct product, {
    required int pharmacyId,
    bool clearExisting = false,
  }) {
    if (state.pharmacyId != null &&
        state.pharmacyId != pharmacyId &&
        !clearExisting) {
      return false;
    }

    final items = state.pharmacyId == pharmacyId && !clearExisting
        ? List<PharmacyCartItem>.of(state.items)
        : <PharmacyCartItem>[];
    final index = items.indexWhere((item) => item.product.id == product.id);
    if (index == -1) {
      items.add(PharmacyCartItem(product: product, quantity: 1));
    } else {
      items[index] = items[index].copyWith(quantity: items[index].quantity + 1);
    }
    state = PharmacyCartState(pharmacyId: pharmacyId, items: items);
    return true;
  }

  void updateQuantity(int productId, int quantity) {
    if (quantity <= 0) {
      remove(productId);
      return;
    }

    state = state.copyWith(
      items: state.items
          .map(
            (item) => item.product.id == productId
                ? item.copyWith(quantity: quantity)
                : item,
          )
          .toList(growable: false),
    );
  }

  void remove(int productId) {
    final items = state.items
        .where((item) => item.product.id != productId)
        .toList(growable: false);
    state = items.isEmpty
        ? const PharmacyCartState()
        : state.copyWith(items: items);
  }

  void attachPrescription(PharmacyPrescription prescription) {
    state = state.copyWith(prescription: prescription);
  }

  void updateNotes(String value) {
    state = state.copyWith(notes: value);
  }

  void updateDeliveryAddress(String value) {
    state = state.copyWith(deliveryAddress: value);
  }

  void clear() {
    state = const PharmacyCartState();
  }
}

class PrescriptionUploadState {
  const PrescriptionUploadState({
    this.isUploading = false,
    this.prescription,
    this.error,
  });

  final bool isUploading;
  final PharmacyPrescription? prescription;
  final ApiError? error;

  PrescriptionUploadState copyWith({
    bool? isUploading,
    PharmacyPrescription? prescription,
    ApiError? error,
    bool clearError = false,
  }) {
    return PrescriptionUploadState(
      isUploading: isUploading ?? this.isUploading,
      prescription: prescription ?? this.prescription,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final prescriptionUploadControllerProvider =
    StateNotifierProvider.autoDispose<
      PrescriptionUploadController,
      PrescriptionUploadState
    >((ref) {
      return PrescriptionUploadController(
        ref.watch(pharmacyRepositoryProvider),
      );
    });

class PrescriptionUploadController
    extends StateNotifier<PrescriptionUploadState> {
  PrescriptionUploadController(PharmacyRepository repository)
    : _uploadPrescription = UploadPrescription(repository),
      super(const PrescriptionUploadState());

  final UploadPrescription _uploadPrescription;

  Future<PharmacyPrescription?> upload(
    UploadPrescriptionRequest request,
  ) async {
    state = state.copyWith(isUploading: true, clearError: true);
    final result = await _uploadPrescription(request);
    return result.when(
      success: (prescription) {
        state = state.copyWith(
          isUploading: false,
          prescription: prescription,
          clearError: true,
        );
        return prescription;
      },
      failure: (failure) {
        state = state.copyWith(isUploading: false, error: failure.error);
        return null;
      },
    );
  }
}

enum PharmacyOrderFilter {
  all,
  review,
  awaitingPayment,
  paid,
  preparing,
  delivered,
  rejected,
}

class PharmacyOrdersState {
  const PharmacyOrdersState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.selectedFilter = PharmacyOrderFilter.all,
  });

  final List<PharmacyOrder> items;
  final bool isLoading;
  final ApiError? error;
  final PharmacyOrderFilter selectedFilter;

  List<PharmacyOrder> get filteredItems {
    return switch (selectedFilter) {
      PharmacyOrderFilter.all => items,
      PharmacyOrderFilter.review =>
        items
            .where((item) => item.status == PharmacyOrderStatus.pharmacyReview)
            .toList(growable: false),
      PharmacyOrderFilter.awaitingPayment =>
        items
            .where(
              (item) =>
                  item.status == PharmacyOrderStatus.accepted ||
                  item.status == PharmacyOrderStatus.awaitingPayment ||
                  item.canPay,
            )
            .toList(growable: false),
      PharmacyOrderFilter.paid =>
        items
            .where((item) => item.status == PharmacyOrderStatus.paid)
            .toList(growable: false),
      PharmacyOrderFilter.preparing =>
        items.where((item) => item.status.isPreparing).toList(growable: false),
      PharmacyOrderFilter.delivered =>
        items
            .where((item) => item.status == PharmacyOrderStatus.delivered)
            .toList(growable: false),
      PharmacyOrderFilter.rejected =>
        items
            .where((item) => item.status.isRejectedOrCancelled)
            .toList(growable: false),
    };
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  PharmacyOrdersState copyWith({
    List<PharmacyOrder>? items,
    bool? isLoading,
    ApiError? error,
    PharmacyOrderFilter? selectedFilter,
    bool clearError = false,
  }) {
    return PharmacyOrdersState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      selectedFilter: selectedFilter ?? this.selectedFilter,
    );
  }
}

final pharmacyOrdersControllerProvider =
    StateNotifierProvider.autoDispose<
      PharmacyOrdersController,
      PharmacyOrdersState
    >((ref) {
      return PharmacyOrdersController(ref.watch(pharmacyRepositoryProvider))
        ..load();
    });

class PharmacyOrdersController extends StateNotifier<PharmacyOrdersState> {
  PharmacyOrdersController(PharmacyRepository repository)
    : _getOrders = GetMyPharmacyOrders(repository),
      super(const PharmacyOrdersState());

  final GetMyPharmacyOrders _getOrders;

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

  void selectFilter(PharmacyOrderFilter filter) {
    state = state.copyWith(selectedFilter: filter);
  }
}

class CreatePharmacyOrderState {
  const CreatePharmacyOrderState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  CreatePharmacyOrderState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return CreatePharmacyOrderState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final createPharmacyOrderControllerProvider =
    StateNotifierProvider.autoDispose<
      CreatePharmacyOrderController,
      CreatePharmacyOrderState
    >((ref) {
      return CreatePharmacyOrderController(
        ref.watch(pharmacyRepositoryProvider),
      );
    });

class CreatePharmacyOrderController
    extends StateNotifier<CreatePharmacyOrderState> {
  CreatePharmacyOrderController(PharmacyRepository repository)
    : _createOrder = CreatePharmacyOrder(repository),
      super(const CreatePharmacyOrderState());

  final CreatePharmacyOrder _createOrder;

  Future<PharmacyOrder?> create(CreatePharmacyOrderRequest request) async {
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

class PharmacyOrderDetailsState {
  const PharmacyOrderDetailsState({
    this.isLoading = false,
    this.isCreatingPayment = false,
    this.order,
    this.paymentStatus,
    this.error,
  });

  final bool isLoading;
  final bool isCreatingPayment;
  final PharmacyOrder? order;
  final PaymentStatusDetails? paymentStatus;
  final ApiError? error;

  PharmacyOrderDetailsState copyWith({
    bool? isLoading,
    bool? isCreatingPayment,
    PharmacyOrder? order,
    PaymentStatusDetails? paymentStatus,
    ApiError? error,
    bool clearError = false,
  }) {
    return PharmacyOrderDetailsState(
      isLoading: isLoading ?? this.isLoading,
      isCreatingPayment: isCreatingPayment ?? this.isCreatingPayment,
      order: order ?? this.order,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final pharmacyOrderDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<PharmacyOrderDetailsController, PharmacyOrderDetailsState, int>((
      ref,
      orderId,
    ) {
      return PharmacyOrderDetailsController(
        orderId,
        ref.watch(pharmacyRepositoryProvider),
        ref.watch(paymentsRepositoryProvider),
      )..load();
    });

class PharmacyOrderDetailsController
    extends StateNotifier<PharmacyOrderDetailsState> {
  PharmacyOrderDetailsController(
    this.orderId,
    PharmacyRepository pharmacyRepository,
    PaymentsRepository paymentRepository,
  ) : _getDetails = GetPharmacyOrderDetails(pharmacyRepository),
      _createPayment = CreatePharmacyOrderPayment(pharmacyRepository),
      _getPaymentStatus = GetPaymentStatus(paymentRepository),
      super(const PharmacyOrderDetailsState());

  final int orderId;
  final GetPharmacyOrderDetails _getDetails;
  final CreatePharmacyOrderPayment _createPayment;
  final GetPaymentStatus _getPaymentStatus;

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

  Future<void> _loadPaymentStatus(int? paymentId) async {
    if (paymentId == null) return;
    final result = await _getPaymentStatus(paymentId);
    state = result.when(
      success: (status) => state.copyWith(paymentStatus: status),
      failure: (failure) => state.copyWith(error: failure.error),
    );
  }
}
