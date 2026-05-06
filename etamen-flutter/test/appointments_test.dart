import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/appointments/data/datasources/appointments_remote_data_source.dart';
import 'package:etamen_app/features/appointments/data/models/appointment_details_model.dart';
import 'package:etamen_app/features/appointments/data/models/appointment_model.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/data/models/cancel_appointment_request.dart';
import 'package:etamen_app/features/appointments/data/repositories/appointments_repository_impl.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment_details.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';
import 'package:etamen_app/features/appointments/presentation/providers/my_appointments_controller.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('Appointment model parses list item with nullable backend fields', () {
    final appointment = AppointmentModel.fromJson({
      'id': 55,
      'appointment_number': 'APT-1',
      'doctor_profile_id': 7,
      'appointment_slot_id': 88,
      'consultation_type': 'clinic',
      'price': '250.00',
      'currency': 'EGP',
      'status': 'pending_payment',
      'payment_id': 200,
      'doctor': {
        'name_ar': 'Dr Test',
        'specialty': {'name_ar': 'Cardiology'},
      },
      'appointment_slot': {'starts_at': '2026-05-05T10:00:00.000000Z'},
    });

    expect(appointment.id, 55);
    expect(appointment.status, AppointmentStatus.pendingPayment);
    expect(appointment.paymentId, 200);
    expect(appointment.doctorName, 'Dr Test');
    expect(appointment.specialty, 'Cardiology');
  });

  test('AppointmentDetails parses history and safe details', () {
    final details = AppointmentDetailsModel.fromJson({
      'id': 55,
      'doctor_profile_id': 7,
      'appointment_slot_id': 88,
      'consultation_type': 'online',
      'price': '250.00',
      'currency': 'EGP',
      'status': 'confirmed',
      'problem_description': 'Headache',
      'status_histories': [
        {
          'from_status': 'pending_payment',
          'to_status': 'confirmed',
          'reason': 'payment verified',
          'created_at': '2026-05-05T10:00:00.000000Z',
        },
      ],
    });

    expect(details.status, AppointmentStatus.confirmed);
    expect(details.problemDescription, 'Headache');
    expect(details.statusHistory, hasLength(1));
  });

  test('AppointmentStatus enum maps cancelled and unknown safely', () {
    expect(
      AppointmentStatus.fromWire('cancelled'),
      AppointmentStatus.cancelled,
    );
    expect(AppointmentStatus.fromWire('no_show'), AppointmentStatus.noShow);
    expect(AppointmentStatus.fromWire('other'), AppointmentStatus.unknown);
  });

  test(
    'appointments repository parses list and cancel calls correct endpoint',
    () async {
      final remote = FakeAppointmentsRemoteDataSource();
      final repository = AppointmentsRepositoryImpl(remote);

      final list = await repository.getMyAppointments();
      expect(list, isA<ApiSuccess>());
      list.when(
        success: (items) => expect(items.first.id, 1),
        failure: (_) => fail('Expected success'),
      );

      final result = await repository.cancel(
        appointmentId: 1,
        request: const CancelAppointmentRequest(reason: 'Changed plans'),
      );

      expect(result, isA<ApiSuccess>());
      expect(remote.cancelledAppointmentId, 1);
      expect(remote.cancelReason, 'Changed plans');
    },
  );

  test('cancel request excludes ownership and status fields', () {
    final json = const CancelAppointmentRequest(reason: 'Later').toJson();

    expect(json['reason'], 'Later');
    expect(json.containsKey('patient_user_id'), false);
    expect(json.containsKey('provider_id'), false);
    expect(json.containsKey('status'), false);
    expect(json.containsKey('payment_status'), false);
  });

  test('my appointments filter logic is local and safe', () {
    const state = MyAppointmentsState(
      selectedFilter: AppointmentListFilter.pendingPayment,
      items: [
        Appointment(
          id: 1,
          doctorProfileId: 7,
          appointmentSlotId: 8,
          consultationType: ConsultationType.clinic,
          price: '250.00',
          currency: 'EGP',
          status: AppointmentStatus.pendingPayment,
        ),
        Appointment(
          id: 2,
          doctorProfileId: 7,
          appointmentSlotId: 9,
          consultationType: ConsultationType.clinic,
          price: '0.00',
          currency: 'EGP',
          status: AppointmentStatus.confirmed,
        ),
      ],
    );

    expect(state.filteredItems, hasLength(1));
    expect(state.filteredItems.first.id, 1);
  });
}

class FakeAppointmentsRemoteDataSource implements AppointmentsRemoteDataSource {
  int? cancelledAppointmentId;
  String? cancelReason;

  @override
  Future<ApiResult<AppointmentModel>> book(
    BookAppointmentRequest request,
  ) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<AppointmentDetailsModel>> cancel({
    required int appointmentId,
    required CancelAppointmentRequest request,
  }) async {
    cancelledAppointmentId = appointmentId;
    cancelReason = request.reason;
    return ApiSuccess(
      AppointmentDetailsModel.fromJson({
        'id': appointmentId,
        'doctor_profile_id': 7,
        'appointment_slot_id': 8,
        'consultation_type': 'clinic',
        'price': '250.00',
        'currency': 'EGP',
        'status': 'cancelled_by_patient',
      }),
    );
  }

  @override
  Future<ApiResult<AppointmentDetailsModel>> getDetails(
    int appointmentId,
  ) async {
    return ApiSuccess(
      AppointmentDetailsModel.fromJson({
        'id': appointmentId,
        'doctor_profile_id': 7,
        'appointment_slot_id': 8,
        'consultation_type': 'clinic',
        'price': '250.00',
        'currency': 'EGP',
        'status': 'confirmed',
      }),
    );
  }

  @override
  Future<ApiResult<List<AppointmentModel>>> getMyAppointments({
    int page = 1,
    int perPage = 20,
  }) async {
    return ApiSuccess([
      AppointmentModel.fromJson({
        'id': 1,
        'doctor_profile_id': 7,
        'appointment_slot_id': 8,
        'consultation_type': 'clinic',
        'price': '250.00',
        'currency': 'EGP',
        'status': 'confirmed',
      }),
    ]);
  }
}

class FakeAppointmentsRepository implements AppointmentsRepository {
  bool shouldFailCancel = false;
  int cancelCalls = 0;

  @override
  Future<ApiResult<Appointment>> book(BookAppointmentRequest request) async {
    return const ApiFailure(
      ApiError(message: 'Not used', type: ApiErrorType.unknown),
    );
  }

  @override
  Future<ApiResult<AppointmentDetails>> cancel({
    required int appointmentId,
    required CancelAppointmentRequest request,
  }) async {
    cancelCalls++;
    if (shouldFailCancel) {
      return const ApiFailure(
        ApiError(message: 'Blocked', type: ApiErrorType.forbidden),
      );
    }

    return ApiSuccess(
      AppointmentDetailsModel.fromJson({
        'id': appointmentId,
        'doctor_profile_id': 7,
        'appointment_slot_id': 8,
        'consultation_type': 'clinic',
        'price': '250.00',
        'currency': 'EGP',
        'status': 'cancelled_by_patient',
      }),
    );
  }

  @override
  Future<ApiResult<AppointmentDetails>> getDetails(int appointmentId) async {
    return ApiSuccess(
      AppointmentDetailsModel.fromJson({
        'id': appointmentId,
        'doctor_profile_id': 7,
        'appointment_slot_id': 8,
        'consultation_type': 'clinic',
        'price': '250.00',
        'currency': 'EGP',
        'status': 'confirmed',
      }),
    );
  }

  @override
  Future<ApiResult<List<Appointment>>> getMyAppointments({
    int page = 1,
    int perPage = 20,
  }) async {
    return const ApiSuccess([]);
  }
}
