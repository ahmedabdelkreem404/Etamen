import 'package:etamen_app/features/doctors/data/models/doctor_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('DoctorModel parses avatar and rating fields safely', () {
    final doctor = DoctorModel.fromJson({
      'id': 10,
      'name_ar': 'د. أحمد التجريبي',
      'name_en': 'Dr Ahmed Demo',
      'is_active': true,
      'primary_branch_name': 'عيادة مدينة نصر',
      'primary_area_name': 'مدينة نصر',
      'primary_city_name': 'القاهرة',
      'doctor_profile': {
        'id': 3,
        'avatar_url': 'http://127.0.0.1:8000/legacy-doctorfinder/demo.png',
        'rating_average': '4.7',
        'reviews_count': 3,
        'consultation_fee': '300.00',
        'years_of_experience': 8,
        'specialties': [
          {'name_ar': 'قلب وأوعية دموية', 'name_en': 'Cardiology'},
        ],
      },
      'branches': [],
    });

    expect(doctor.avatarUrl, contains('/legacy-doctorfinder/demo.png'));
    expect(doctor.ratingAverage, 4.7);
    expect(doctor.reviewsCount, 3);
    expect(doctor.branches.single, contains('القاهرة'));
  });

  test('DoctorModel tolerates missing avatar and rating fields', () {
    final doctor = DoctorModel.fromJson({
      'id': 11,
      'name_en': 'Doctor Pending Visuals',
      'is_active': true,
      'doctor_profile': {'id': 4},
      'branches': [],
    });

    expect(doctor.avatarUrl, isNull);
    expect(doctor.ratingAverage, isNull);
    expect(doctor.reviewsCount, 0);
  });
}
