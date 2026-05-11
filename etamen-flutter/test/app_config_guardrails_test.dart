import 'package:etamen_app/core/config/app_config.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('QA login affordances are local environment only', () {
    expect(AppConfig.isLocalEnvironmentValue('local'), isTrue);
    expect(AppConfig.isLocalEnvironmentValue(' LOCAL '), isTrue);
    expect(AppConfig.isLocalEnvironmentValue('staging'), isFalse);
    expect(AppConfig.isLocalEnvironmentValue('production'), isFalse);
    expect(AppConfig.isLocalEnvironmentValue('unset'), isFalse);
    expect(AppConfig.isLocalEnvironmentValue(''), isFalse);
    expect(AppConfig.isLocalEnvironmentValue(null), isFalse);
  });
}
