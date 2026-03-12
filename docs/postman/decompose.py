#!/usr/bin/env python3
"""
decompose.py — Run ONCE.
Decomposes docs/postman_collection.json into individual source files under docs/postman/.
Response bodies are stored as real JSON objects (not escaped strings), making them
human-readable and easy to edit.

After running this, use build.py to regenerate postman_collection.json.
"""

import json
import os
import re

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
COLLECTION_PATH = os.path.join(SCRIPT_DIR, "../postman_collection.json")

# Folder name in collection → subdirectory name
FOLDER_DIRS = {
    "Authentication": "auth",
    "Users":          "users",
    "Rooms":          "rooms",
    "Facilities":     "facilities",
    "Reservations":   "reservations",
}


def parse_body(body):
    """Parse a response body string to a JSON object if possible."""
    if not isinstance(body, str) or not body.strip():
        return body
    try:
        return json.loads(body)
    except Exception:
        return body   # keep as plain string (e.g. HTML error pages)


def process_item(item: dict) -> dict:
    """Deep-copy item, parsing all response body strings to JSON objects."""
    result = dict(item)
    if "response" in result:
        parsed_responses = []
        for resp in result["response"]:
            r = dict(resp)
            if "body" in r:
                r["body"] = parse_body(r["body"])
            parsed_responses.append(r)
        result["response"] = parsed_responses
    return result


def slugify(name: str) -> str:
    s = name.lower()
    s = re.sub(r"[^a-z0-9]+", "_", s)
    return s.strip("_")


def main():
    with open(COLLECTION_PATH, "r", encoding="utf-8") as f:
        collection = json.load(f)

    # --- write _meta.json -------------------------------------------------
    meta = {
        "info":         collection["info"],
        "variable":     collection["variable"],
        "folder_order": list(FOLDER_DIRS.keys()),
    }
    meta_path = os.path.join(SCRIPT_DIR, "_meta.json")
    with open(meta_path, "w", encoding="utf-8") as f:
        json.dump(meta, f, indent=4, ensure_ascii=False)
    print(f"✅  _meta.json")

    # --- write each endpoint file -----------------------------------------
    for top_folder in collection["item"]:
        folder_name = top_folder["name"]
        dir_name    = FOLDER_DIRS.get(folder_name, slugify(folder_name))
        folder_dir  = os.path.join(SCRIPT_DIR, dir_name)
        os.makedirs(folder_dir, exist_ok=True)

        items = top_folder.get("item", [])
        for i, item in enumerate(items, 1):
            processed = process_item(item)
            filename  = f"{i:02d}_{slugify(item['name'])}.json"
            filepath  = os.path.join(folder_dir, filename)
            with open(filepath, "w", encoding="utf-8") as f:
                json.dump(processed, f, indent=4, ensure_ascii=False)
            print(f"✅  {dir_name}/{filename}")

    print("\nDone! Edit source files freely, then run  python3 build.py  to rebuild.")


if __name__ == "__main__":
    main()
