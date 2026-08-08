import 'package:flutter/material.dart';
import 'package:just_audio/just_audio.dart';
import 'package:path_provider/path_provider.dart';
import 'package:record/record.dart';

import '../models/song.dart';
import '../services/api_client.dart';

class KaraokeScreen extends StatefulWidget {
  const KaraokeScreen({super.key, required this.song, required this.api, required this.appUserId});
  final Song song;
  final ApiClient api;
  final String appUserId;

  @override
  State<KaraokeScreen> createState() => _KaraokeScreenState();
}

class _KaraokeScreenState extends State<KaraokeScreen> {
  final _player = AudioPlayer();
  final _recorder = AudioRecorder();
  bool _recording = false;
  String? _recordingPath;
  String? _result;

  @override
  void initState() {
    super.initState();
    if (widget.song.streamUrl != null) _player.setUrl(widget.song.streamUrl!);
  }

  Future<void> _toggle() async {
    if (_recording) {
      final path = await _recorder.stop();
      await _player.pause();
      setState(() => _recording = false);
      if (path != null) {
        setState(() => _result = '採点用データを送信中…');
        final id = await widget.api.uploadRecording(songId: widget.song.id, path: path, appUserId: widget.appUserId);
        setState(() => _result = '採点処理中（録音 #$id）');
        _pollScore(id);
      }
      return;
    }
    if (!await _recorder.hasPermission()) return;
    final directory = await getTemporaryDirectory();
    final path = '${directory.path}/utaone_${widget.song.id}_${DateTime.now().millisecondsSinceEpoch}.m4a';
    _recordingPath = path;
    await _recorder.start(const RecordConfig(encoder: AudioEncoder.aacLc), path: _recordingPath!);
    await _player.seek(Duration.zero);
    await _player.play();
    setState(() => _recording = true);
  }

  Future<void> _pollScore(int recordingId) async {
    for (var attempt = 0; attempt < 60 && mounted; attempt++) {
      await Future<void>.delayed(const Duration(seconds: 2));
      final recording = await widget.api.recording(recordingId, widget.appUserId);
      if (recording['status'] == 'completed') {
        setState(() => _result = 'スコア ${recording['score']} 点');
        return;
      }
      if (recording['status'] == 'failed') {
        setState(() => _result = '採点に失敗しました');
        return;
      }
    }
    if (mounted) setState(() => _result = '採点処理を継続しています');
  }

  @override
  void dispose() {
    _player.dispose();
    _recorder.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.song.title)),
      body: StreamBuilder<Duration>(
        stream: _player.positionStream,
        builder: (context, snapshot) {
          final position = snapshot.data?.inMilliseconds ?? 0;
          final current = widget.song.timeline.where((line) => position >= line.startMs && position < line.endMs).firstOrNull;
          return Column(
            children: [
              Expanded(
                child: Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: AnimatedSwitcher(
                      duration: const Duration(milliseconds: 180),
                      child: Text(
                        current?.text ?? '♪',
                        key: ValueKey(current?.startMs),
                        textAlign: TextAlign.center,
                        style: Theme.of(context).textTheme.headlineMedium,
                      ),
                    ),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(32),
                child: FilledButton.icon(
                  onPressed: _toggle,
                  icon: Icon(_recording ? Icons.stop : Icons.mic),
                  label: Text(_recording ? '歌唱を終了' : '歌唱を開始'),
                ),
              ),
              if (_result != null) Padding(padding: const EdgeInsets.only(bottom: 16), child: Text(_result!)),
            ],
          );
        },
      ),
    );
  }
}
