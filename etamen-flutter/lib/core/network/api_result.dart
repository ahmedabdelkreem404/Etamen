import 'package:etamen_app/core/network/api_error.dart';

sealed class ApiResult<T> {
  const ApiResult();

  bool get isSuccess => this is ApiSuccess<T>;

  R when<R>({
    required R Function(T data) success,
    required R Function(ApiFailure<T> failure) failure,
  }) {
    final current = this;
    if (current is ApiSuccess<T>) {
      return success(current.data);
    }

    return failure(current as ApiFailure<T>);
  }
}

class ApiSuccess<T> extends ApiResult<T> {
  const ApiSuccess(this.data, {this.message});

  final T data;
  final String? message;
}

class ApiFailure<T> extends ApiResult<T> {
  const ApiFailure(this.error);

  final ApiError error;
}
