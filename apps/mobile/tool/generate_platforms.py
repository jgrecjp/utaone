"""Generate Flutter platform projects without overwriting UtaOne app code."""

from __future__ import annotations

import argparse
import shutil
import subprocess
from pathlib import Path

from prepare_platforms import configure_android, configure_ios


ROOT = Path(__file__).resolve().parents[1]


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--platforms", default="android,ios")
    args = parser.parse_args()

    flutter = shutil.which("flutter")
    if not flutter:
        raise SystemExit("flutter command was not found")

    main_file = ROOT / "lib" / "main.dart"
    original_main = main_file.read_bytes()
    widget_test = ROOT / "test" / "widget_test.dart"
    original_widget_test = widget_test.read_bytes() if widget_test.exists() else None

    try:
        subprocess.run(
            [
                flutter,
                "create",
                f"--platforms={args.platforms}",
                "--org",
                "jp.utaone",
                "--project-name",
                "utaone",
                ".",
            ],
            cwd=ROOT,
            check=True,
        )
    finally:
        main_file.write_bytes(original_main)
        if original_widget_test is None:
            widget_test.unlink(missing_ok=True)
        else:
            widget_test.write_bytes(original_widget_test)

    configure_android()
    configure_ios()


if __name__ == "__main__":
    main()
