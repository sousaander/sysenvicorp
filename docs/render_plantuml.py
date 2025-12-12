#!/usr/bin/env python3
import zlib, sys
from urllib import request

ENCODING_MAP = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_"

def encode6bit(b):
    return ENCODING_MAP[b & 0x3F]

def append3bytes(b1,b2,b3):
    c1 = b1 >> 2
    c2 = ((b1 & 0x3) << 4) | (b2 >> 4)
    c3 = ((b2 & 0xF) << 2) | (b3 >> 6)
    c4 = b3 & 0x3F
    return encode6bit(c1) + encode6bit(c2) + encode6bit(c3) + encode6bit(c4)

def encode64(data: bytes) -> str:
    res = []
    i = 0
    while i < len(data):
        b1 = data[i]
        b2 = data[i+1] if i+1 < len(data) else 0
        b3 = data[i+2] if i+2 < len(data) else 0
        res.append(append3bytes(b1,b2,b3))
        i += 3
    return ''.join(res)

def plantuml_encode(text: str) -> str:
    compressed = zlib.compress(text.encode('utf-8'))
    # strip zlib header and checksum
    compressed = compressed[2:-4]
    return encode64(compressed)


def fetch_and_save(encoded, fmt='png', out_path='docs/architecture.png'):
    url = f'https://www.plantuml.com/plantuml/{fmt}/{encoded}'
    print('Fetching', url)
    resp = request.urlopen(url)
    data = resp.read()
    with open(out_path, 'wb') as f:
        f.write(data)
    print('Saved to', out_path)

if __name__ == '__main__':
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument('input', help='Input .puml file')
    parser.add_argument('--png', help='Output PNG path', default='docs/architecture.png')
    parser.add_argument('--svg', help='Output SVG path', default='docs/architecture.svg')
    args = parser.parse_args()

    with open(args.input, 'r', encoding='utf-8') as f:
        text = f.read()

    enc = plantuml_encode(text)
    try:
        fetch_and_save(enc, 'png', args.png)
    except Exception as e:
        print('PNG failed:', e)
    try:
        fetch_and_save(enc, 'svg', args.svg)
    except Exception as e:
        print('SVG failed:', e)

    print('Done')
