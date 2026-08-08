import 'package:flutter/material.dart';

import '../models/song.dart';
import '../services/api_client.dart';
import '../services/subscription_service.dart';
import 'karaoke_screen.dart';

class SongListScreen extends StatefulWidget {
  const SongListScreen({super.key, required this.api, required this.subscriptions, required this.appUserId});

  final ApiClient api;
  final SubscriptionService subscriptions;
  final String appUserId;

  @override
  State<SongListScreen> createState() => _SongListScreenState();
}

class _SongListScreenState extends State<SongListScreen> {
  late Future<List<Song>> _songs;
  bool _premium = false;

  @override
  void initState() {
    super.initState();
    _songs = widget.api.songs();
    widget.subscriptions.isPremium().then((value) {
      if (mounted) setState(() => _premium = value);
    });
  }

  Future<void> _subscribe() async {
    final active = await widget.subscriptions.purchaseCurrentOffering();
    if (mounted) setState(() => _premium = active);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('UtaOne'),
        actions: [
          if (!_premium)
            TextButton(onPressed: _subscribe, child: const Text('プレミアム'))
          else
            const Padding(
              padding: EdgeInsets.all(16),
              child: Icon(Icons.workspace_premium),
            ),
        ],
      ),
      body: FutureBuilder<List<Song>>(
        future: _songs,
        builder: (context, snapshot) {
          if (snapshot.hasError) return Center(child: Text('読み込みに失敗しました\n${snapshot.error}'));
          if (!snapshot.hasData) return const Center(child: CircularProgressIndicator());
          return RefreshIndicator(
            onRefresh: () async => setState(() => _songs = widget.api.songs()),
            child: ListView.separated(
              itemCount: snapshot.data!.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final song = snapshot.data![index];
                return ListTile(
                  leading: const CircleAvatar(child: Icon(Icons.music_note)),
                  title: Text(song.title),
                  subtitle: Text('${song.artist}  難易度 ${song.difficulty}'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () async {
                    if (!_premium) {
                      await _subscribe();
                      if (!_premium) return;
                    }
                    final detail = await widget.api.song(song.id);
                    if (!context.mounted) return;
                    await Navigator.of(context).push(MaterialPageRoute(
                      builder: (_) => KaraokeScreen(song: detail, api: widget.api, appUserId: widget.appUserId),
                    ));
                  },
                );
              },
            ),
          );
        },
      ),
    );
  }
}
