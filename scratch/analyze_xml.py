import json

log_path = r"C:\Users\kiddp\.gemini\antigravity-ide\brain\1d524ea2-45a4-4da4-b39d-8b79bf63bb4f\.system_generated\logs\transcript.jsonl"

with open(log_path, 'r', encoding='utf-8') as f:
    for line in f:
        data = json.loads(line)
        if data.get("type") == "USER_INPUT":
            content = data["content"]
            xml_start = content.find("<mxGraphModel")
            xml_end = content.rfind("</mxGraphModel>")
            if xml_start != -1 and xml_end != -1:
                xml_content = content[xml_start:xml_end + len("</mxGraphModel>")]
                with open("scratch/extracted.xml", "w", encoding="utf-8") as out:
                    out.write(xml_content)
                print("XML extracted and saved successfully to scratch/extracted.xml")
            else:
                # Let's see what is there
                print(f"Could not find start/end. Start: {xml_start}, End: {xml_end}")
                # Write content to a file to inspect
                with open("scratch/raw_content.txt", "w", encoding="utf-8") as out:
                    out.write(content)
            break
