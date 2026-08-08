import unittest

from utaone_worker.alignment import fallback_alignment, normalize_lyrics


class AlignmentTest(unittest.TestCase):
    def test_normalize_lyrics_removes_blank_lines(self) -> None:
        self.assertEqual(normalize_lyrics("A\r\n\r\n B \n"), ["A", "B"])

    def test_fallback_alignment_is_ordered_and_covers_duration(self) -> None:
        segments = fallback_alignment(["短い", "少し長い歌詞"], 10)
        self.assertEqual(segments[0].start_ms, 0)
        self.assertEqual(segments[0].end_ms, segments[1].start_ms)
        self.assertEqual(segments[-1].end_ms, 10_000)
