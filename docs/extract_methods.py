#!/usr/bin/env python3
import os
import re
import csv
import json

ROOT = os.path.dirname(os.path.dirname(__file__))
CONTROLLERS_DIR = os.path.join(ROOT, "app", "controllers")
OUTPUT_CSV = os.path.join(ROOT, "docs", "controllers_methods.csv")
OUTPUT_JSON = os.path.join(ROOT, "docs", "controllers_methods.json")

class_re = re.compile(r"class\s+([A-Za-z0-9_]+)")
method_re = re.compile(r"public\s+function\s+([a-zA-Z0-9_]+)\s*\(")

controllers = []

for fname in sorted(os.listdir(CONTROLLERS_DIR)):
    if not fname.endswith(".php"):
        continue
    path = os.path.join(CONTROLLERS_DIR, fname)
    with open(path, "r", encoding="utf-8") as f:
        text = f.read()
    classname_match = class_re.search(text)
    classname = (
        classname_match.group(1) if classname_match else fname.replace(".php", "")
    )
    methods = method_re.findall(text)
    # remove magic methods or constructor if desired
    methods = [m for m in methods if m != "__construct"]
    controllers.append(
        {
            "controller": classname,
            "file": os.path.relpath(path, ROOT),
            "methods": methods,
        }
    )

# write csv
with open(OUTPUT_CSV, "w", newline="", encoding="utf-8") as csvfile:
    writer = csv.writer(csvfile)
    writer.writerow(["controller", "file", "method"])
    for c in controllers:
        for m in c["methods"]:
            writer.writerow([c["controller"], c["file"], m])

# write json
with open(OUTPUT_JSON, "w", encoding="utf-8") as jf:
    json.dump(controllers, jf, indent=2, ensure_ascii=False)

print("Wrote", OUTPUT_CSV, "and", OUTPUT_JSON)
