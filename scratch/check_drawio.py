import xml.etree.ElementTree as ET

file_path = "reso use case(1)(3)(3)(3).drawio"

try:
    tree = ET.parse(file_path)
    root = tree.getroot()
    print("Parsing successful!")
    
    # Check for diagrams
    diagrams = root.findall(".//diagram")
    print(f"Found {len(diagrams)} diagram pages:")
    for idx, d in enumerate(diagrams):
        print(f"Page {idx}: ID={d.get('id')}, Name={d.get('name')}")
        
    # Search for Bookmark or the specific IDs
    found_ids = []
    for elem in root.iter():
        elem_id = elem.get("id")
        if elem_id and "dAhemJaqEyVSEdy-pGvr" in elem_id:
            found_ids.append(elem_id)
            
    print(f"Found {len(found_ids)} matching element IDs.")
    if found_ids:
        print("First 10 matching IDs:", found_ids[:10])
except Exception as e:
    print("Error:", e)
