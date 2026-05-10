import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_json_helpers.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_models.dart';

class FitnessRemoteDataSource {
  const FitnessRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<List<GymModel>>> getGyms() {
    return _client.get<List<GymModel>>(
      ApiEndpoints.gyms,
      queryParameters: const {'per_page': 50},
      parser: (raw) =>
          fitnessList(raw).map(GymModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<GymModel>> getGym(int gymId) {
    return _client.get<GymModel>(
      ApiEndpoints.gym(gymId),
      parser: (raw) => GymModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<List<GymMembershipPlanModel>>> getGymMembershipPlans(
    int gymId,
  ) {
    return _client.get<List<GymMembershipPlanModel>>(
      ApiEndpoints.gymMembershipPlans(gymId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => fitnessList(raw)
          .map(GymMembershipPlanModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<GymClassModel>>> getGymClasses(int gymId) {
    return _client.get<List<GymClassModel>>(
      ApiEndpoints.gymClasses(gymId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => fitnessList(raw)
          .map(GymClassModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<GymBookingModel>>> getGymBookings() {
    return _client.get<List<GymBookingModel>>(
      ApiEndpoints.gymBookings,
      queryParameters: const {'per_page': 30},
      parser: (raw) => fitnessList(
        raw,
      ).map(GymBookingModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<GymBookingModel>> getGymBooking(int bookingId) {
    return _client.get<GymBookingModel>(
      ApiEndpoints.gymBooking(bookingId),
      parser: (raw) => GymBookingModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<GymBookingModel>> createGymBooking(
    CreateGymBookingRequest request,
  ) {
    return _client.post<GymBookingModel>(
      ApiEndpoints.gymBookings,
      data: request.toJson(),
      parser: (raw) => GymBookingModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<GymBookingModel>> cancelGymBooking(int bookingId) {
    return _client.post<GymBookingModel>(
      ApiEndpoints.gymBookingCancel(bookingId),
      parser: (raw) => GymBookingModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<List<CoachModel>>> getCoaches() {
    return _client.get<List<CoachModel>>(
      ApiEndpoints.coaches,
      queryParameters: const {'per_page': 50},
      parser: (raw) =>
          fitnessList(raw).map(CoachModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<CoachModel>> getCoach(int coachId) {
    return _client.get<CoachModel>(
      ApiEndpoints.coach(coachId),
      parser: (raw) => CoachModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<List<CoachSessionTypeModel>>> getCoachSessionTypes(
    int coachId,
  ) {
    return _client.get<List<CoachSessionTypeModel>>(
      ApiEndpoints.coachSessionTypes(coachId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => fitnessList(raw)
          .map(CoachSessionTypeModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<CoachAvailabilitySlotModel>>> getCoachAvailability(
    int coachId,
  ) {
    return _client.get<List<CoachAvailabilitySlotModel>>(
      ApiEndpoints.coachAvailability(coachId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => fitnessList(raw)
          .map(CoachAvailabilitySlotModel.fromJson)
          .where((item) => item.isAvailable)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<CoachPackageModel>>> getCoachPackages(int coachId) {
    return _client.get<List<CoachPackageModel>>(
      ApiEndpoints.coachPackages(coachId),
      queryParameters: const {'per_page': 50},
      parser: (raw) => fitnessList(raw)
          .map(CoachPackageModel.fromJson)
          .where((item) => item.isActive)
          .toList(growable: false),
    );
  }

  Future<ApiResult<List<CoachBookingModel>>> getCoachBookings() {
    return _client.get<List<CoachBookingModel>>(
      ApiEndpoints.coachBookings,
      queryParameters: const {'per_page': 30},
      parser: (raw) => fitnessList(
        raw,
      ).map(CoachBookingModel.fromJson).toList(growable: false),
    );
  }

  Future<ApiResult<CoachBookingModel>> getCoachBooking(int bookingId) {
    return _client.get<CoachBookingModel>(
      ApiEndpoints.coachBooking(bookingId),
      parser: (raw) => CoachBookingModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<CoachBookingModel>> createCoachBooking(
    CreateCoachBookingRequest request,
  ) {
    return _client.post<CoachBookingModel>(
      ApiEndpoints.coachBookings,
      data: request.toJson(),
      parser: (raw) => CoachBookingModel.fromJson(unwrapFitnessMap(raw)),
    );
  }

  Future<ApiResult<CoachBookingModel>> cancelCoachBooking(int bookingId) {
    return _client.post<CoachBookingModel>(
      ApiEndpoints.coachBookingCancel(bookingId),
      parser: (raw) => CoachBookingModel.fromJson(unwrapFitnessMap(raw)),
    );
  }
}
