import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'screens/song_list_screen.dart';
import 'services/api_client.dart';
import 'services/subscription_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final preferences = await SharedPreferences.getInstance();
  var appUserId = preferences.getString('app_user_id');
  if (appUserId == null) {
    appUserId = 'device-${DateTime.now().microsecondsSinceEpoch}';
    await preferences.setString('app_user_id', appUserId);
  }
  final subscriptions = SubscriptionService();
  await subscriptions.configure(appUserId: appUserId);
  runApp(UtaOneApp(subscriptions: subscriptions, appUserId: appUserId));
}

class UtaOneApp extends StatelessWidget {
  const UtaOneApp({super.key, required this.subscriptions, required this.appUserId});
  final SubscriptionService subscriptions;
  final String appUserId;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'UtaOne',
      theme: ThemeData(colorSchemeSeed: const Color(0xff7457ff), useMaterial3: true),
      home: SongListScreen(api: ApiClient(), subscriptions: subscriptions, appUserId: appUserId),
    );
  }
}
