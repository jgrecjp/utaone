from __future__ import annotations

import argparse
import time

from utaone_api.config import Settings

from .runner import run_once


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--once", action="store_true")
    parser.add_argument("--poll-seconds", type=float, default=2.0)
    args = parser.parse_args()
    settings = Settings.from_env()
    if args.once:
        run_once(settings)
        return
    while True:
        if not run_once(settings):
            time.sleep(max(args.poll_seconds, 0.2))


if __name__ == "__main__":
    main()
