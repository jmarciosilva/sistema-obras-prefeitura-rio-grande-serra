import '../../../core/api/api_client.dart';
import '../../../core/config/app_config.dart';
import '../models/login_response.dart';
import '../models/user.dart';

class AuthApi {
  AuthApi(this._client);

  final ApiClient _client;

  Future<LoginResponse> login(String email, String password) async {
    final json = await _client.post(
      '/login',
      data: {
        'email': email,
        'password': password,
        'device_name': AppConfig.deviceName,
      },
    );
    return LoginResponse.fromJson(json);
  }

  Future<User> me() async => User.fromJson(await _client.get('/me'));

  Future<void> logout() => _client.post('/logout');
}
