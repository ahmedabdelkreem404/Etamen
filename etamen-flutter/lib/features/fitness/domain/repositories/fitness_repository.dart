import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_models.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';

abstract class FitnessRepository {
  Future<ApiResult<List<Gym>>> getGyms();

  Future<ApiResult<Gym>> getGym(int gymId);

  Future<ApiResult<List<GymMembershipPlan>>> getGymMembershipPlans(int gymId);

  Future<ApiResult<List<GymClass>>> getGymClasses(int gymId);

  Future<ApiResult<List<GymBooking>>> getGymBookings();

  Future<ApiResult<GymBooking>> getGymBooking(int bookingId);

  Future<ApiResult<GymBooking>> createGymBooking(
    CreateGymBookingRequest request,
  );

  Future<ApiResult<GymBooking>> cancelGymBooking(int bookingId);

  Future<ApiResult<List<Coach>>> getCoaches();

  Future<ApiResult<Coach>> getCoach(int coachId);

  Future<ApiResult<List<CoachSessionType>>> getCoachSessionTypes(int coachId);

  Future<ApiResult<List<CoachAvailabilitySlot>>> getCoachAvailability(
    int coachId,
  );

  Future<ApiResult<List<CoachPackage>>> getCoachPackages(int coachId);

  Future<ApiResult<List<CoachBooking>>> getCoachBookings();

  Future<ApiResult<CoachBooking>> getCoachBooking(int bookingId);

  Future<ApiResult<CoachBooking>> createCoachBooking(
    CreateCoachBookingRequest request,
  );

  Future<ApiResult<CoachBooking>> cancelCoachBooking(int bookingId);
}
