from pathlib import Path
import tempfile
import unittest

from utaone_api.database import connect, initialize_database


class DatabaseTest(unittest.TestCase):
    def test_database_initializes(self) -> None:
        with tempfile.TemporaryDirectory() as directory:
            database = Path(directory) / "utaone.sqlite3"
            initialize_database(database)
            with connect(database) as connection:
                connection.execute("INSERT INTO songs(title, artist) VALUES (?, ?)", ("Song", "Artist"))
                self.assertEqual(connection.execute("SELECT COUNT(*) FROM songs").fetchone()[0], 1)
