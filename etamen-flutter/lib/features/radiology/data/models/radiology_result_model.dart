import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';

class RadiologyResultModel extends RadiologyResult {
  const RadiologyResultModel({
    required super.id,
    required super.orderId,
    required super.resultType,
    super.titleAr,
    super.titleEn,
    super.notesAr,
    super.notesEn,
    super.isVisibleToPatient,
    super.fileName,
    super.mimeType,
    super.fileSize,
    super.downloadUrl,
    super.uploadedAt,
  });

  factory RadiologyResultModel.fromJson(Map<String, dynamic> json) {
    final file = asRadiologyMap(json['file']);
    return RadiologyResultModel(
      id: radiologyInt(json['id']) ?? 0,
      orderId:
          radiologyInt(json['radiology_order_id'] ?? json['order_id']) ?? 0,
      resultType: (json['result_type'] ?? 'other').toString(),
      titleAr: json['title_ar']?.toString(),
      titleEn: json['title_en']?.toString(),
      notesAr: json['notes_ar']?.toString(),
      notesEn: json['notes_en']?.toString(),
      isVisibleToPatient: radiologyBool(json['is_visible_to_patient']),
      fileName: (json['file_name'] ?? file?['original_name'] ?? file?['name'])
          ?.toString(),
      mimeType: (json['mime_type'] ?? file?['mime_type'])?.toString(),
      fileSize: radiologyInt(json['size'] ?? file?['size']),
      downloadUrl: json['download_url']?.toString(),
      uploadedAt: DateTime.tryParse(
        (json['uploaded_at'] ?? json['created_at'] ?? '').toString(),
      ),
    );
  }
}
