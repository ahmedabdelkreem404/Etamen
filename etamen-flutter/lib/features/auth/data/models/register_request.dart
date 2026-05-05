class RegisterRequest {
  const RegisterRequest({
    required this.name,
    required this.email,
    required this.password,
    required this.passwordConfirmation,
    this.phone,
  });

  final String name;
  final String email;
  final String password;
  final String passwordConfirmation;
  final String? phone;

  Map<String, dynamic> toJson() {
    return {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      if (phone != null && phone!.isNotEmpty) 'phone': phone,
    };
  }
}
