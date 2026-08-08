class LyricSegment {
  const LyricSegment({
    required this.text,
    required this.startMs,
    required this.endMs,
  });

  final String text;
  final int startMs;
  final int endMs;

  factory LyricSegment.fromJson(Map<String, dynamic> json) => LyricSegment(
        text: json['text'] as String,
        startMs: json['start_ms'] as int,
        endMs: json['end_ms'] as int,
      );
}

class Song {
  const Song({
    required this.id,
    required this.title,
    required this.artist,
    required this.difficulty,
    this.streamUrl,
    this.timeline = const [],
  });

  final int id;
  final String title;
  final String artist;
  final int difficulty;
  final String? streamUrl;
  final List<LyricSegment> timeline;

  factory Song.fromJson(Map<String, dynamic> json) => Song(
        id: json['id'] as int,
        title: json['title'] as String,
        artist: json['artist'] as String,
        difficulty: json['difficulty'] as int,
        streamUrl: json['stream_url'] as String?,
        timeline: (json['timeline'] as List<dynamic>? ?? [])
            .map((item) => LyricSegment.fromJson(item as Map<String, dynamic>))
            .toList(),
      );
}
