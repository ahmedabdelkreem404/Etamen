import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_log_request.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/data/models/medication_adherence_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_log_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_refill_event_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_reminder_model.dart';
import 'package:etamen_app/features/medications/data/models/medication_reminder_time_model.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('MedicationReminderModel parses nullable medication fields', () {
    final reminder = MedicationReminderModel.fromJson({
      'id': 10,
      'medication_name': 'Vitamin D',
      'dosage': '1000',
      'dosage_unit': 'IU',
      'frequency_type': 'once_daily',
      'status': 'active',
      'refill_enabled': true,
      'times': [
        {'id': 1, 'medication_reminder_id': 10, 'time_of_day': '08:00'},
      ],
    });

    expect(reminder.id, 10);
    expect(reminder.frequencyType, MedicationFrequencyType.onceDaily);
    expect(reminder.status, MedicationReminderStatus.active);
    expect(reminder.dosageText, '1000 IU');
    expect(reminder.times, hasLength(1));
  });

  test('MedicationReminderTimeModel parses time safely', () {
    final time = MedicationReminderTimeModel.fromJson({
      'id': 1,
      'medication_reminder_id': 10,
      'time_of_day': '20:30',
      'label': 'night',
      'is_active': true,
    });

    expect(time.timeOfDay, '20:30');
    expect(time.label, 'night');
  });

  test('MedicationLogModel parses log actions', () {
    final log = MedicationLogModel.fromJson({
      'id': 5,
      'medication_reminder_id': 10,
      'scheduled_for': '2026-05-06T08:00:00Z',
      'action': 'taken',
      'taken_at': '2026-05-06T08:05:00Z',
    });

    expect(log.action, MedicationLogAction.taken);
    expect(log.scheduledFor, isNotNull);
  });

  test('MedicationAdherenceModel parses counts and avoids advice wording', () {
    final adherence = MedicationAdherenceModel.fromJson({
      'total_scheduled': 10,
      'taken_count': 8,
      'skipped_count': 1,
      'missed_count': 1,
      'adherence_percentage': '80',
      'disclaimer': 'tracking only',
    });

    expect(adherence.takenCount, 8);
    expect(adherence.adherencePercentage, 80);
    expect(adherence.disclaimer?.contains('treatment'), false);
  });

  test('MedicationRefillEventModel parses refill event type', () {
    final event = MedicationRefillEventModel.fromJson({
      'id': 3,
      'medication_reminder_id': 10,
      'event_type': 'refill_done',
      'event_date': '2026-05-06',
    });

    expect(event.eventType, MedicationRefillEventType.refillDone);
  });

  test('Medication enum mapping tolerates unknown values', () {
    expect(
      MedicationFrequencyType.fromWire('twice_daily'),
      MedicationFrequencyType.twiceDaily,
    );
    expect(
      MedicationFrequencyType.fromWire('bad'),
      MedicationFrequencyType.unknown,
    );
    expect(
      MedicationReminderStatus.fromWire('paused'),
      MedicationReminderStatus.paused,
    );
    expect(MedicationLogAction.fromWire('missed'), MedicationLogAction.missed);
  });

  test('CreateMedicationReminderRequest excludes forbidden fields', () {
    final request = CreateMedicationReminderRequest(
      medicationName: 'Vitamin D',
      dosage: '1000',
      dosageUnit: 'IU',
      frequencyType: MedicationFrequencyType.onceDaily,
      startDate: '2026-05-06',
      times: const [ReminderTimeInput(timeOfDay: '08:00')],
    );
    final json = request.toJson();

    expect(json['medication_name'], 'Vitamin D');
    expect(json['times'], [
      {'time_of_day': '08:00'},
    ]);
    expect(json.containsKey('patient_user_id'), false);
    expect(json.containsKey('user_id'), false);
    expect(json.containsKey('source'), false);
    expect(json.containsKey('status'), false);
    expect(json.containsKey('adherence'), false);
    expect(json.containsKey('provider_id'), false);
    expect(json.containsKey('doctor_id'), false);
    expect(json.containsKey('diagnosis'), false);
    expect(json.containsKey('treatment'), false);
  });

  test(
    'CreateMedicationLogRequest excludes patient missed source and status',
    () {
      final json = CreateMedicationLogRequest(
        action: MedicationLogAction.taken,
        scheduledFor: DateTime.utc(2026, 5, 6, 8),
        takenAt: DateTime.utc(2026, 5, 6, 8, 5),
      ).toJson();

      expect(json['action'], 'taken');
      expect(json.containsKey('patient_user_id'), false);
      expect(json.containsKey('missed'), false);
      expect(json.containsKey('source'), false);
      expect(json.containsKey('status'), false);
    },
  );

  test('UI-created medication log requests cannot serialize missed action', () {
    expect(
      () => CreateMedicationLogRequest(
        action: MedicationLogAction.missed,
        scheduledFor: DateTime.utc(2026, 5, 6, 8),
      ).toJson(),
      throwsStateError,
    );

    final quickJson = QuickMedicationLogRequest(
      scheduledFor: DateTime.utc(2026, 5, 6, 8),
      notes: 'Skipped by patient',
    ).toJson();

    expect(quickJson.containsKey('action'), false);
    expect(quickJson.containsKey('missed'), false);
    expect(quickJson.containsKey('patient_user_id'), false);
  });

  test('frequency validation matches backend rules', () {
    String? validate(
      MedicationFrequencyType type,
      List<ReminderTimeInput> times,
    ) {
      return MedicationReminderValidator.validate(
        CreateMedicationReminderRequest(
          medicationName: 'Test',
          frequencyType: type,
          startDate: '2026-05-06',
          times: times,
        ),
      );
    }

    expect(validate(MedicationFrequencyType.onceDaily, const []), isNotNull);
    expect(
      validate(MedicationFrequencyType.onceDaily, const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ]),
      isNull,
    );
    expect(
      validate(MedicationFrequencyType.twiceDaily, const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ]),
      isNotNull,
    );
    expect(
      validate(MedicationFrequencyType.customTimes, const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ]),
      isNull,
    );
    expect(
      MedicationReminderValidator.validate(
        const CreateMedicationReminderRequest(
          medicationName: 'Test',
          frequencyType: MedicationFrequencyType.everyXHours,
          startDate: '2026-05-06',
          intervalHours: 8,
        ),
      ),
      isNull,
    );
    expect(validate(MedicationFrequencyType.asNeeded, const []), isNull);
  });

  test('today controller marks taken and refreshes', () async {
    final repository = FakeMedicationsRepository();
    final controller = TodayMedicationsController(repository);

    await controller.load();
    final ok = await controller.markTaken(controller.state.items.single);

    expect(ok, true);
    expect(repository.markTakenCount, 1);
    expect(repository.todayLoadCount, 2);
  });

  test('today controller marks skipped and refreshes', () async {
    final repository = FakeMedicationsRepository();
    final controller = TodayMedicationsController(repository);

    await controller.load();
    final ok = await controller.markSkipped(controller.state.items.single);

    expect(ok, true);
    expect(repository.markSkippedCount, 1);
    expect(repository.todayLoadCount, 2);
  });

  test('adherence controller loads organization-only summary', () async {
    final controller = MedicationAdherenceController(
      FakeMedicationsRepository(),
    );

    await controller.load();

    expect(controller.state.adherence?.totalScheduled, 1);
    expect(controller.state.error, isNull);
  });

  test('reminders controller local filter works', () async {
    final controller = MedicationRemindersController(
      FakeMedicationsRepository(),
    );

    await controller.load();
    controller.selectFilter(ReminderFilter.active);

    expect(controller.state.filteredItems, hasLength(1));
    expect(
      controller.state.filteredItems.single.status,
      MedicationReminderStatus.active,
    );
  });
}

class FakeMedicationsRepository implements MedicationsRepository {
  int todayLoadCount = 0;
  int markTakenCount = 0;
  int markSkippedCount = 0;

  @override
  Future<ApiResult<MedicationReminder>> cancelReminder(int reminderId) {
    return Future.value(ApiSuccess(_reminders.last));
  }

  @override
  Future<ApiResult<MedicationReminder>> createReminder(
    CreateMedicationReminderRequest request,
  ) {
    return Future.value(ApiSuccess(_reminders.first));
  }

  @override
  Future<ApiResult<MedicationLog>> createLog(
    int reminderId,
    CreateMedicationLogRequest request,
  ) {
    return Future.value(ApiSuccess(_log(reminderId, request.action)));
  }

  @override
  Future<ApiResult<MedicationAdherence>> getAdherence() {
    return Future.value(
      const ApiSuccess(MedicationAdherence(totalScheduled: 1, takenCount: 1)),
    );
  }

  @override
  Future<ApiResult<List<MedicationLog>>> getLogs({int? reminderId}) {
    return Future.value(
      ApiSuccess([_log(reminderId ?? 1, MedicationLogAction.taken)]),
    );
  }

  @override
  Future<ApiResult<MedicationReminder>> getReminder(int reminderId) {
    return Future.value(ApiSuccess(_reminders.first));
  }

  @override
  Future<ApiResult<List<MedicationReminder>>> getReminders({
    MedicationReminderStatus? status,
  }) {
    return Future.value(ApiSuccess(_reminders));
  }

  @override
  Future<ApiResult<List<MedicationRefillEvent>>> getRefills() {
    return Future.value(const ApiSuccess([]));
  }

  @override
  Future<ApiResult<List<MedicationScheduleItem>>> getToday() {
    todayLoadCount++;
    return Future.value(
      ApiSuccess([
        MedicationScheduleItem(
          reminderId: 1,
          medicationName: 'Vitamin D',
          scheduledFor: DateTime.utc(2026, 5, 6, 8),
        ),
      ]),
    );
  }

  @override
  Future<ApiResult<List<MedicationScheduleItem>>> getUpcoming({int days = 7}) {
    return getToday();
  }

  @override
  Future<ApiResult<MedicationLog>> markSkipped(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    markSkippedCount++;
    return Future.value(
      ApiSuccess(_log(reminderId, MedicationLogAction.skipped)),
    );
  }

  @override
  Future<ApiResult<MedicationLog>> markTaken(
    int reminderId,
    QuickMedicationLogRequest request,
  ) {
    markTakenCount++;
    return Future.value(
      ApiSuccess(_log(reminderId, MedicationLogAction.taken)),
    );
  }

  @override
  Future<ApiResult<MedicationReminder>> pauseReminder(int reminderId) {
    return Future.value(ApiSuccess(_reminders.first));
  }

  @override
  Future<ApiResult<MedicationRefillEvent>> recordRefillDone(
    int reminderId, {
    String? notes,
  }) {
    return Future.value(
      ApiSuccess(
        MedicationRefillEvent(
          id: 1,
          medicationReminderId: reminderId,
          eventType: MedicationRefillEventType.refillDone,
        ),
      ),
    );
  }

  @override
  Future<ApiResult<MedicationRefillEvent>> recordRefillSkipped(
    int reminderId, {
    String? notes,
  }) {
    return Future.value(
      ApiSuccess(
        MedicationRefillEvent(
          id: 1,
          medicationReminderId: reminderId,
          eventType: MedicationRefillEventType.refillSkipped,
        ),
      ),
    );
  }

  @override
  Future<ApiResult<MedicationReminder>> resumeReminder(int reminderId) {
    return Future.value(ApiSuccess(_reminders.first));
  }

  @override
  Future<ApiResult<MedicationReminder>> updateReminder(
    int reminderId,
    CreateMedicationReminderRequest request,
  ) {
    return Future.value(ApiSuccess(_reminders.first));
  }

  static List<MedicationReminder> get _reminders => const [
    MedicationReminder(
      id: 1,
      medicationName: 'Vitamin D',
      frequencyType: MedicationFrequencyType.onceDaily,
      status: MedicationReminderStatus.active,
    ),
    MedicationReminder(
      id: 2,
      medicationName: 'Paused med',
      frequencyType: MedicationFrequencyType.asNeeded,
      status: MedicationReminderStatus.paused,
    ),
  ];

  static MedicationLog _log(int reminderId, MedicationLogAction action) {
    return MedicationLog(
      id: 1,
      medicationReminderId: reminderId,
      action: action,
    );
  }
}
