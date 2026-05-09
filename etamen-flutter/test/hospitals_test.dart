import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/hospitals/data/models/hospital_department_model.dart';
import 'package:etamen_app/features/hospitals/data/models/hospital_model.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('HospitalModel parses safe public hospital summary', () {
    final hospital = HospitalModel.fromJson({
      'id': 4,
      'name_ar': 'مستشفى اطمن التخصصي',
      'description_ar': 'Demo hospital',
      'phone': '01000006001',
      'primary_area_name': 'مدينة نصر',
      'primary_city_name': 'القاهرة',
      'primary_address': 'شارع تجريبي',
      'latitude': '30.0561000',
      'longitude': '31.3300000',
      'departments_count': 5,
      'doctors_count': 5,
      'emergency_available': true,
      'has_outpatient': true,
      'has_inpatient': true,
      'has_icu': true,
      'has_ambulance': true,
      'branches': [
        {
          'id': 8,
          'name_ar': 'فرع مدينة نصر',
          'address_ar': 'شارع تجريبي',
          'city': {'name_ar': 'القاهرة'},
          'area': {'name_ar': 'مدينة نصر'},
          'is_main': true,
          'is_24_hours': true,
        },
      ],
    });

    expect(hospital.id, 4);
    expect(hospital.name, 'مستشفى اطمن التخصصي');
    expect(hospital.locationLabel, 'مدينة نصر - القاهرة');
    expect(hospital.departmentsCount, 5);
    expect(hospital.doctorsCount, 5);
    expect(hospital.emergencyAvailable, true);
    expect(hospital.branches.single.is24Hours, true);
  });

  test('HospitalDepartmentModel parses counts and names safely', () {
    final department = HospitalDepartmentModel.fromJson({
      'id': 10,
      'name_ar': 'قلب وأوعية دموية',
      'description_ar': 'قسم تجريبي',
      'doctors_count': 2,
    });

    expect(department.id, 10);
    expect(department.name, 'قلب وأوعية دموية');
    expect(department.doctorsCount, 2);
  });

  test('hospital endpoints and routes are stable', () {
    expect(ApiEndpoints.hospitals, '/hospitals');
    expect(ApiEndpoints.hospital(4), '/hospitals/4');
    expect(ApiEndpoints.hospitalDepartments(4), '/hospitals/4/departments');
    expect(ApiEndpoints.hospitalDoctors(4), '/hospitals/4/doctors');
    expect(
      ApiEndpoints.hospitalDepartmentDoctors(4, 10),
      '/hospitals/4/departments/10/doctors',
    );
    expect(RouteNames.hospitalDetails(4), '/hospitals/4');
    expect(
      RouteNames.hospitalDepartmentDoctors(4, 10),
      '/hospitals/4/departments/10/doctors',
    );
  });
}
