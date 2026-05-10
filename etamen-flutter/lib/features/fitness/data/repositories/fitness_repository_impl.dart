import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/fitness/data/datasources/fitness_remote_data_source.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_models.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';
import 'package:etamen_app/features/fitness/domain/repositories/fitness_repository.dart';

class FitnessRepositoryImpl implements FitnessRepository {
  const FitnessRepositoryImpl(this._remoteDataSource);

  final FitnessRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<List<Gym>>> getGyms() => _remoteDataSource.getGyms();

  @override
  Future<ApiResult<Gym>> getGym(int gymId) => _remoteDataSource.getGym(gymId);

  @override
  Future<ApiResult<List<GymMembershipPlan>>> getGymMembershipPlans(int gymId) {
    return _remoteDataSource.getGymMembershipPlans(gymId);
  }

  @override
  Future<ApiResult<List<GymClass>>> getGymClasses(int gymId) {
    return _remoteDataSource.getGymClasses(gymId);
  }

  @override
  Future<ApiResult<List<GymBooking>>> getGymBookings() {
    return _remoteDataSource.getGymBookings();
  }

  @override
  Future<ApiResult<GymBooking>> getGymBooking(int bookingId) {
    return _remoteDataSource.getGymBooking(bookingId);
  }

  @override
  Future<ApiResult<GymBooking>> createGymBooking(
    CreateGymBookingRequest request,
  ) {
    return _remoteDataSource.createGymBooking(request);
  }

  @override
  Future<ApiResult<GymBooking>> cancelGymBooking(int bookingId) {
    return _remoteDataSource.cancelGymBooking(bookingId);
  }

  @override
  Future<ApiResult<List<Coach>>> getCoaches() {
    return _remoteDataSource.getCoaches();
  }

  @override
  Future<ApiResult<Coach>> getCoach(int coachId) {
    return _remoteDataSource.getCoach(coachId);
  }

  @override
  Future<ApiResult<List<CoachSessionType>>> getCoachSessionTypes(int coachId) {
    return _remoteDataSource.getCoachSessionTypes(coachId);
  }

  @override
  Future<ApiResult<List<CoachAvailabilitySlot>>> getCoachAvailability(
    int coachId,
  ) {
    return _remoteDataSource.getCoachAvailability(coachId);
  }

  @override
  Future<ApiResult<List<CoachPackage>>> getCoachPackages(int coachId) {
    return _remoteDataSource.getCoachPackages(coachId);
  }

  @override
  Future<ApiResult<List<CoachBooking>>> getCoachBookings() {
    return _remoteDataSource.getCoachBookings();
  }

  @override
  Future<ApiResult<CoachBooking>> getCoachBooking(int bookingId) {
    return _remoteDataSource.getCoachBooking(bookingId);
  }

  @override
  Future<ApiResult<CoachBooking>> createCoachBooking(
    CreateCoachBookingRequest request,
  ) {
    return _remoteDataSource.createCoachBooking(request);
  }

  @override
  Future<ApiResult<CoachBooking>> cancelCoachBooking(int bookingId) {
    return _remoteDataSource.cancelCoachBooking(bookingId);
  }
}
