"""Apply UtaOne permissions to Flutter-generated Android and iOS projects."""

from __future__ import annotations

import plistlib
import xml.etree.ElementTree as ET
from pathlib import Path


ROOT = Path(__file__).resolve().parents[1]
ANDROID_NS = "http://schemas.android.com/apk/res/android"
ET.register_namespace("android", ANDROID_NS)


def configure_android() -> None:
    manifest = ROOT / "android" / "app" / "src" / "main" / "AndroidManifest.xml"
    if not manifest.exists():
        return
    tree = ET.parse(manifest)
    root = tree.getroot()
    existing = {item.get(f"{{{ANDROID_NS}}}name") for item in root.findall("uses-permission")}
    for permission in (
        "android.permission.INTERNET",
        "android.permission.RECORD_AUDIO",
        "com.android.vending.BILLING",
    ):
        if permission not in existing:
            node = ET.Element("uses-permission")
            node.set(f"{{{ANDROID_NS}}}name", permission)
            root.insert(0, node)
    tree.write(manifest, encoding="utf-8", xml_declaration=True)


def configure_ios() -> None:
    info_plist = ROOT / "ios" / "Runner" / "Info.plist"
    if not info_plist.exists():
        return
    with info_plist.open("rb") as source:
        values = plistlib.load(source)
    values["NSMicrophoneUsageDescription"] = "歌唱を録音して採点するためにマイクを使用します。"
    with info_plist.open("wb") as destination:
        plistlib.dump(values, destination, sort_keys=False)


if __name__ == "__main__":
    configure_android()
    configure_ios()
