import os
import struct
import zlib
import urllib.parse

brain_dir = r"C:\Users\kiddp\.gemini\antigravity-ide\brain\1d524ea2-45a4-4da4-b39d-8b79bf63bb4f"
png_files = [f for f in os.listdir(brain_dir) if f.endswith(".png")]

def parse_png_chunks(path):
    print(f"\nParsing PNG: {path}")
    with open(path, 'rb') as f:
        sig = f.read(8)
        if sig != b'\x89PNG\r\n\x1a\n':
            print("Not a valid PNG file signature.")
            return
        
        while True:
            chunk_len_bytes = f.read(4)
            if not chunk_len_bytes:
                break
            chunk_len = struct.unpack('>I', chunk_len_bytes)[0]
            chunk_type = f.read(4)
            chunk_data = f.read(chunk_len)
            crc = f.read(4)
            
            # Print text chunks
            if chunk_type in (b'tEXt', b'zTXt', b'iTXt'):
                print(f"Found chunk: {chunk_type.decode('ascii')} (length {chunk_len})")
                
                if chunk_type == b'tEXt':
                    # Keyword \0 Text
                    parts = chunk_data.split(b'\x00', 1)
                    if len(parts) == 2:
                        keyword = parts[0].decode('latin1', errors='ignore')
                        text = parts[1].decode('latin1', errors='ignore')
                        print(f"  Keyword: {keyword}")
                        print(f"  Text preview: {text[:100]}...")
                        if keyword == "mxfile":
                            save_xml(path, text)
                            
                elif chunk_type == b'zTXt':
                    # Keyword \0 CompressionMethod \0 CompressedText
                    parts = chunk_data.split(b'\x00', 2)
                    if len(parts) >= 2:
                        keyword = parts[0].decode('latin1', errors='ignore')
                        comp_method = parts[1][0] if len(parts[1]) > 0 else 0
                        comp_text = parts[2]
                        print(f"  Keyword: {keyword}, CompMethod: {comp_method}")
                        try:
                            decompressed = zlib.decompress(comp_text).decode('utf-8', errors='ignore')
                            print(f"  Decompressed preview: {decompressed[:100]}...")
                            if keyword == "mxfile":
                                save_xml(path, decompressed)
                        except Exception as e:
                            print(f"  Decompression error: {e}")
                            
            if chunk_type == b'IEND':
                break

def save_xml(png_path, content):
    png_name = os.path.basename(png_path)
    out_path = f"scratch/extracted_{png_name}.xml"
    # Content might be URL-encoded or standard XML
    if content.startswith("%3C"):
        content = urllib.parse.unquote(content)
    with open(out_path, "w", encoding="utf-8") as out:
        out.write(content)
    print(f"Saved extracted XML to {out_path}")

for png in png_files:
    parse_png_chunks(os.path.join(brain_dir, png))
