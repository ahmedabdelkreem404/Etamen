import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_models.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('Gym model parses safe public fields and counts', () {
    final gym = GymModel.fromJson({
      'id': 10,
      'type': 'gym',
      'name_ar': 'جيم اطمن',
      'name_en': 'Etamen Gym',
      'primary_area_name': 'مدينة نصر',
      'primary_city_name': 'القاهرة',
      'gym_profile': {
        'men_allowed': true,
        'women_allowed': true,
        'has_classes': true,
        'has_personal_training': true,
      },
      'branches': [
        {
          'id': 3,
          'provider_id': 10,
          'address_ar': 'شارع تجريبي',
          'area_name': 'مدينة نصر',
          'city_name': 'القاهرة',
        },
      ],
      'membership_plans_count': 2,
      'classes_count': 2,
    });

    expect(gym.id, 10);
    expect(gym.name(false), 'Etamen Gym');
    expect(gym.locationLabel, 'مدينة نصر - القاهرة');
    expect(gym.branches.single.address(true), contains('شارع تجريبي'));
    expect(gym.hasClasses, true);
    expect(gym.membershipPlansCount, 2);
  });

  test('Gym booking request never sends trusted totals or status', () {
    const request = CreateGymBookingRequest(membershipPlanId: 5, notes: 'Test');
    final json = request.toJson();

    expect(json['membership_plan_id'], 5);
    expect(json['notes'], 'Test');
    expect(json.containsKey('total_amount'), false);
    expect(json.containsKey('status'), false);
    expect(json.containsKey('payment_id'), false);
  });

  test('GymBookingModel parses payment id and friendly status', () {
    final booking = GymBookingModel.fromJson({
      'id': 70,
      'booking_number': 'GYM-70',
      'status': 'confirmed',
      'total_amount': '600.00',
      'payment_id': 501,
      'provider': {'id': 10, 'name_ar': 'جيم اطمن'},
      'membership_plan': {
        'id': 5,
        'provider_id': 10,
        'name_ar': 'شهري',
        'duration_days': 30,
        'price': '600.00',
      },
    });

    expect(booking.status, GymBookingStatus.confirmed);
    expect(booking.status.friendlyLabel(true), 'مؤكد');
    expect(booking.paymentId, 501);
    expect(booking.summary(true), 'شهري');
  });

  test('Coach models parse sessions and bookings safely', () {
    final coach = CoachModel.fromJson({
      'id': 20,
      'type': 'fitness_coach',
      'name_ar': 'كابتن أحمد التجريبي',
      'coach_profile': {
        'coach_type': 'fitness',
        'experience_years': 8,
        'online_coaching_enabled': true,
      },
      'session_types_count': 2,
      'availability_count': 3,
    });
    final session = CoachSessionTypeModel.fromJson({
      'id': 8,
      'provider_id': 20,
      'name_ar': 'جلسة تقييم لياقة',
      'duration_minutes': 45,
      'price': '450.00',
      'session_mode': 'gym',
    });
    final booking = CoachBookingModel.fromJson({
      'id': 71,
      'booking_number': 'COACH-71',
      'coach_provider_id': 20,
      'session_type_id': 8,
      'status': 'pending_payment_review',
      'total_amount': '450.00',
      'payment': {'id': 502, 'currency': 'EGP'},
      'session_type': {
        'id': 8,
        'provider_id': 20,
        'name_ar': 'جلسة تقييم لياقة',
        'duration_minutes': 45,
        'price': '450.00',
        'session_mode': 'gym',
      },
    });

    expect(coach.coachType, 'fitness');
    expect(coach.onlineCoachingEnabled, true);
    expect(session.sessionMode, 'gym');
    expect(booking.status, CoachBookingStatus.pendingPaymentReview);
    expect(booking.paymentId, 502);
    expect(booking.summary(true), 'جلسة تقييم لياقة');
  });

  test('Coach booking request never sends trusted totals or status', () {
    const request = CreateCoachBookingRequest(
      coachProviderId: 20,
      sessionTypeId: 8,
      availabilitySlotId: 9,
      patientGoal: 'Improve fitness',
    );
    final json = request.toJson();

    expect(json['coach_provider_id'], 20);
    expect(json['session_type_id'], 8);
    expect(json['availability_slot_id'], 9);
    expect(json.containsKey('total_amount'), false);
    expect(json.containsKey('status'), false);
    expect(json.containsKey('payment_id'), false);
  });

  test('Fitness endpoints and routes are stable', () {
    expect(ApiEndpoints.gyms, '/gyms');
    expect(ApiEndpoints.gym(10), '/gyms/10');
    expect(ApiEndpoints.gymMembershipPlans(10), '/gyms/10/membership-plans');
    expect(ApiEndpoints.gymClasses(10), '/gyms/10/classes');
    expect(ApiEndpoints.gymBookings, '/gym/bookings');
    expect(ApiEndpoints.gymBooking(70), '/gym/bookings/70');
    expect(ApiEndpoints.coaches, '/coaches');
    expect(ApiEndpoints.coach(20), '/coaches/20');
    expect(ApiEndpoints.coachSessionTypes(20), '/coaches/20/session-types');
    expect(ApiEndpoints.coachAvailability(20), '/coaches/20/availability');
    expect(ApiEndpoints.coachPackages(20), '/coaches/20/packages');
    expect(ApiEndpoints.coachBookings, '/coach/bookings');
    expect(ApiEndpoints.coachBooking(71), '/coach/bookings/71');
    expect(RouteNames.gyms, '/gyms');
    expect(RouteNames.gymBookingDetails(70), '/gym/bookings/70');
    expect(RouteNames.coaches, '/coaches');
    expect(RouteNames.coachBookingDetails(71), '/coach/bookings/71');
    expect(
      RouteNames.payment(501, gymBookingId: 70),
      '/payments/501?gymBookingId=70',
    );
    expect(
      RouteNames.payment(502, coachBookingId: 71),
      '/payments/502?coachBookingId=71',
    );
  });
}
