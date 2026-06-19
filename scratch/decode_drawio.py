import xml.etree.ElementTree as ET
import os

file_path = "erd(1)(2).drawio"

if not os.path.exists(file_path):
    print(f"File {file_path} does not exist!")
    exit(1)

try:
    tree = ET.parse(file_path)
    root = tree.getroot()
    
    # Iterate through all mxCells
    for cell in root.findall(".//mxCell"):
        style = cell.get("style", "")
        value = cell.get("value", "")
        vertex = cell.get("vertex")
        
        # Check if it is a table (style has shape=table or it's a container table)
        if vertex == "1" and "shape=table" in style:
            geo = cell.find("mxGeometry")
            if geo is not None:
                x = geo.get("x", "0")
                y = geo.get("y", "0")
                w = geo.get("width", "0")
                h = geo.get("height", "0")
                print(f"Table: {value} | ID: {cell.get('id')} | Position: x={x}, y={y}, w={w}, h={h}")
except Exception as e:
    print("Error:", e)
