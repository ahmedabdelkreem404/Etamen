import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class AppTextStyles {
  const AppTextStyles._();

  static const headline = TextStyle(
    fontSize: 24,
    fontWeight: FontWeight.w700,
    color: AppColors.text,
  );

  static const title = TextStyle(
    fontSize: 18,
    fontWeight: FontWeight.w700,
    color: AppColors.text,
  );

  static const body = TextStyle(
    fontSize: 14,
    height: 1.45,
    color: AppColors.text,
  );

  static const caption = TextStyle(
    fontSize: 12,
    height: 1.35,
    color: AppColors.muted,
  );
}
