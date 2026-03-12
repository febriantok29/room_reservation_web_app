#!/usr/bin/env python3
"""
build.py — Run every time you need to regenerate postman_collection.json.

Usage:
    cd docs/postman
    python3 build.py

Reads all NN_*.json files from each folder sub-directory, converts response
bodies back to escaped JSON strings (required by Postman), and writes
docs/postman_collection.json.

Workflow for adding a new endpoint:
  1. Create  docs/postman/<folder>/NN_endpoint_name.json  (copy any existing as template)
  2. Write the request/response as plain, readable JSON (body = real object, not string)
  3. Run:  python3 docs/postman/build.py
  4. Import the updated postman_collection.json into Postman
"""

import json
import os
import glob

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_PATH = os.path.join(SCRIPT_DIR, "../postman_collection.json")

# Order matters — defines folder order in Postman sidebar
FOLDER_DIRS = {
    "Authentication": "auth",
    "Users":          "users",
    "Rooms":          "rooms",
    "Facilities":     "facilities",
    "Reservations":   "reservations",
}


def serialize_body(body) -> str:
    """Convert body back to a JSON string for Postman format."""
    if isinstance(body, (dict, list)):
        return json.dumps(body, indent=2, ensure_ascii=False)
    if body is None:
        return ""
    return body   # already a plain string


def process_item(item: dict) -> dict:
    """Re-serialize response bodies from JSON objects back to strings."""
    result = dict(item)
    if "response" in result:
        serialized = []
        for resp in result["response"]:
            r = dict(resp)
            if "body" in r:
                r["body"] = serialize_body(r["body"])
            serialized.append(r)
        result["response"] = serialized
    return result


def main():
    meta_path = os.path.join(SCRIPT_DIR, "_meta.json")
    if not os.path.exists(meta_path):
        print("❌  _meta.json not found. Run decompose.py first.")
        return

    with open(meta_path, "r", encoding="utf-8") as f:
        meta = json.load(f)

    folders = []
    total_items = 0

    for folder_name, dir_name in FOLDER_DIRS.items():
        folder_dir = os.path.join(SCRIPT_DIR, dir_name)
        if not os.path.isdir(folder_dir):
            print(f"⚠️   Folder not found: {dir_name} — skipping '{folder_name}'")
            continue

        files = sorted(glob.glob(os.path.join(folder_dir, "*.json")))
        items = []
        for filepath in files:
            with open(filepath, "r", encoding="utf-8") as f:
                item = json.load(f)
            items.append(process_item(item))

        folders.append({"name": folder_name, "item": items})
        total_items += len(items)
        print(f"📁  {folder_name:20s}  {len(items)} items  ({dir_name}/)")

    collection = {
        "info":     meta["info"],
        "item":     folders,
        "variable": meta["variable"],
    }

    with open(OUTPUT_PATH, "w", encoding="utf-8") as f:
        json.dump(collection, f, indent=4, ensure_ascii=False)

    lines = sum(1 for _ in open(OUTPUT_PATH))
    print(f"\n✅  postman_collection.json  ({total_items} endpoints, ~{lines} lines)")


if __name__ == "__main__":
    main()
