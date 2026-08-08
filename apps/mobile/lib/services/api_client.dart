import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/song.dart';

class ApiClient {
  ApiClient({http.Client? client}) : _client = client ?? http.Client();

  static const _baseUrl = String.fromEnvironment(
    'UTAONE_API_URL',
    defaultValue: 'http://127.0.0.1:8000',
  );
  final http.Client _client;

  Future<List<Song>> songs() async {
    final response = await _client.get(Uri.parse('$_baseUrl/v1/songs'));
    _ensureSuccess(response);
    final list = jsonDecode(response.body) as List<dynamic>;
    return list.map((item) => Song.fromJson(item as Map<String, dynamic>)).toList();
  }

  Future<Song> song(int id) async {
    final response = await _client.get(Uri.parse('$_baseUrl/v1/songs/$id'));
    _ensureSuccess(response);
    return Song.fromJson(jsonDecode(response.body) as Map<String, dynamic>);
  }

  Future<int> uploadRecording({required int songId, required String path, required String appUserId}) async {
    final request = http.MultipartRequest('POST', Uri.parse('$_baseUrl/v1/songs/$songId/recordings'))
      ..headers['X-App-User-Id'] = appUserId
      ..files.add(await http.MultipartFile.fromPath('recording', path));
    final streamed = await _client.send(request);
    final response = await http.Response.fromStream(streamed);
    _ensureSuccess(response);
    return (jsonDecode(response.body) as Map<String, dynamic>)['recording_id'] as int;
  }

  Future<Map<String, dynamic>> recording(int id, String appUserId) async {
    final response = await _client.get(
      Uri.parse('$_baseUrl/v1/recordings/$id'),
      headers: {'X-App-User-Id': appUserId},
    );
    _ensureSuccess(response);
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  static void _ensureSuccess(http.Response response) {
    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw StateError('API error ${response.statusCode}: ${response.body}');
    }
  }
}
