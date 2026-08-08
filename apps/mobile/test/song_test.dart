import 'package:flutter_test/flutter_test.dart';
import 'package:utaone/models/song.dart';

void main() {
  test('song JSON includes synchronized lyric segments', () {
    final song = Song.fromJson({
      'id': 1,
      'title': 'Test Song',
      'artist': 'Test Artist',
      'difficulty': 3,
      'stream_url': 'https://example.com/song.m4a',
      'timeline': [
        {'text': 'hello', 'start_ms': 100, 'end_ms': 500},
      ],
    });

    expect(song.title, 'Test Song');
    expect(song.timeline.single.text, 'hello');
    expect(song.timeline.single.startMs, 100);
  });
}
