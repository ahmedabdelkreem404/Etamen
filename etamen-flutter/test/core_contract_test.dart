import 'package:dio/dio.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_response.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/network/error_mapper.dart';
import 'package:etamen_app/core/storage/token_storage.dart';
import 'package:etamen_app/features/appointments/data/models/book_appointment_request.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/auth/data/datasources/auth_remote_data_source.dart';
import 'package:etamen_app/features/auth/data/models/auth_response.dart';
import 'package:etamen_app/features/auth/data/models/login_request.dart';
import 'package:etamen_app/features/auth/data/models/register_request.dart';
import 'package:etamen_app/features/auth/data/models/user_model.dart';
import 'package:etamen_app/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:etamen_app/features/doctors/data/models/doctor_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('standard response parser reads envelope and validation errors', () {
    final response = ApiResponse<int>.fromJson({
      'success': false,
      'message': 'Validation failed',
      'data': 10,
      'errors': {
        'email': ['The email field is required.'],
      },
    }, (raw) => raw as int);

    expect(response.success, false);
    expect(response.message, 'Validation failed');
    expect(response.data, 10);
    expect(response.errors['email'], ['The email field is required.']);
  });

  test('error mapper converts dio responses to app errors', () {
    final mapper = const ErrorMapper();
    final error = mapper.fromDio(
      DioException(
        requestOptions: RequestOptions(path: '/auth/login'),
        response: Response(
          requestOptions: RequestOptions(path: '/auth/login'),
          statusCode: 422,
          data: {
            'success': false,
            'message': 'The given data was invalid.',
            'data': null,
            'errors': {
              'email': ['Invalid email'],
            },
          },
        ),
        type: DioExceptionType.badResponse,
      ),
    );

    expect(error.type, ApiErrorType.validation);
    expect(error.statusCode, 422);
    expect(error.validationErrors['email'], ['Invalid email']);
  });

  test('token storage abstraction can save read and clear tokens', () async {
    final storage = FakeTokenStorage();

    await storage.saveToken('token-123');
    expect(await storage.readToken(), 'token-123');

    await storage.clearToken();
    expect(await storage.readToken(), isNull);
  });

  test(
    'auth repository stores token on success and returns failure safely',
    () async {
      final storage = FakeTokenStorage();
      final remote = FakeAuthRemoteDataSource();
      final repository = AuthRepositoryImpl(
        remoteDataSource: remote,
        tokenStorage: storage,
      );

      final success = await repository.login(
        const LoginRequest(email: 'patient@example.com', password: 'password'),
      );

      expect(success, isA<ApiSuccess>());
      expect(await storage.readToken(), 'secure-token');

      remote.shouldFail = true;
      final failure = await repository.login(
        const LoginRequest(email: 'bad@example.com', password: 'bad'),
      );

      expect(failure, isA<ApiFailure>());
    },
  );

  test('doctor model tolerates nullable backend fields', () {
    final doctor = DoctorModel.fromJson({
      'id': 1,
      'name_ar': 'د. أحمد',
      'is_active': true,
      'doctor_profile': {
        'id': 5,
        'consultation_fee': '250.00',
        'specialties': [
          {'name_ar': 'قلب'},
        ],
      },
      'branches': [
        {
          'city': {'name_ar': 'القاهرة'},
          'area': {'name_ar': 'مدينة نصر'},
        },
      ],
    });

    expect(doctor.id, 1);
    expect(doctor.doctorProfileId, 5);
    expect(doctor.specialties, ['قلب']);
    expect(doctor.branches.first, 'مدينة نصر - القاهرة');
  });

  test('appointment booking request does not include forbidden fields', () {
    final request = const BookAppointmentRequest(
      doctorProfileId: 7,
      appointmentSlotId: 99,
      consultationType: ConsultationType.clinic,
      problemDescription: 'Headache',
    ).toJson();

    expect(request['doctor_profile_id'], 7);
    expect(request['appointment_slot_id'], 99);
    expect(request['consultation_type'], 'clinic');
    expect(request.containsKey('patient_user_id'), false);
    expect(request.containsKey('user_id'), false);
    expect(request.containsKey('price'), false);
    expect(request.containsKey('status'), false);
    expect(request.containsKey('payment_status'), false);
    expect(request.containsKey('provider_id'), false);
  });
}

class FakeTokenStorage implements TokenStorage {
  String? token;

  @override
  Future<void> clearToken() async {
    token = null;
  }

  @override
  Future<String?> readToken() async {
    return token;
  }

  @override
  Future<void> saveToken(String token) async {
    this.token = token;
  }
}

class FakeAuthRemoteDataSource implements AuthRemoteDataSource {
  bool shouldFail = false;

  @override
  Future<ApiResult<AuthResponse>> login(LoginRequest request) async {
    if (shouldFail) {
      return const ApiFailure(
        ApiError(
          message: 'Invalid credentials',
          type: ApiErrorType.unauthenticated,
        ),
      );
    }

    return const ApiSuccess(
      AuthResponse(
        user: UserModel(
          id: 1,
          email: 'patient@example.com',
          roles: ['patient'],
        ),
        token: 'secure-token',
        tokenType: 'Bearer',
      ),
    );
  }

  @override
  Future<ApiResult<Object?>> logout() async {
    return const ApiSuccess(null);
  }

  @override
  Future<ApiResult<UserModel>> me() async {
    return const ApiSuccess(
      UserModel(id: 1, email: 'patient@example.com', roles: ['patient']),
    );
  }

  @override
  Future<ApiResult<AuthResponse>> register(RegisterRequest request) async {
    return login(
      LoginRequest(email: request.email, password: request.password),
    );
  }
}
