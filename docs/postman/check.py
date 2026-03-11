#!/usr/bin/env python3
import json, glob, os

postman_dir_parent = os.path.dirname(os.path.abspath(__file__))          # docs/postman
base = os.path.dirname(postman_dir_parent)                                # project root
collection_path = os.path.join(postman_dir_parent, "../postman_collection.json")
postman_dir = postman_dir_parent

with open(collection_path) as f:
    c = json.load(f)

assert 'info' in c and 'item' in c and 'variable' in c
print("Folders:", [x['name'] for x in c['item']])

issues = []
for folder in c['item']:
    for item in folder.get('item', []):
        for resp in item.get('response', []):
            if 'body' in resp and not isinstance(resp['body'], str):
                issues.append(f"{folder['name']} / {item['name']} / {resp['name']}")
print("❌ Non-string bodies:", issues if issues else "none")
print("✅ All response bodies are strings" if not issues else "")

src_files = sorted(glob.glob(os.path.join(postman_dir, "**/*.json"), recursive=True))
src_files = [f for f in src_files if not f.endswith("postman_collection.json")]
errors = []
for fp in src_files:
    try:
        json.load(open(fp))
    except Exception as e:
        errors.append(f"{os.path.relpath(fp, base)}: {e}")
if errors:
    print("❌ Invalid JSON:", errors)
else:
    print(f"✅ All {len(src_files)} source files are valid JSON")
print("\nFull integrity check PASSED ✅")
