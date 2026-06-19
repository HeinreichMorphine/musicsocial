import xml.etree.ElementTree as ET
import os

file_path = "erd(1)(2).drawio"
output_path = "erd(1)(2).drawio"

def create_table_row(row_id, parent_id, y, label, is_pk=False, is_fk=False):
    # Determine PK/FK label
    key_val = ""
    if is_pk and is_fk:
        key_val = "PK,FK"
    elif is_pk:
        key_val = "PK"
    elif is_fk:
        key_val = "FK"
        
    style_row = "shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;top=0;left=0;right=0;bottom=0;"
    style_key = "shape=partialRectangle;overflow=hidden;connectable=0;fillColor=none;top=0;left=0;bottom=0;right=0;fontStyle=1;"
    style_val = "shape=partialRectangle;overflow=hidden;connectable=0;fillColor=none;top=0;left=0;bottom=0;right=0;align=left;spacingLeft=6;"
    if is_pk:
        style_val += "fontStyle=5;" # Underlined/italic for PK
        
    row = ET.Element("mxCell", id=row_id, parent=parent_id, style=style_row, value="", vertex="1")
    ET.SubElement(row, "mxGeometry", **{"height": "30", "width": "180", "y": str(y), "as": "geometry"})
    
    key_cell = ET.Element("mxCell", id=f"{row_id}-key", parent=row_id, style=style_key, value=key_val, vertex="1")
    ET.SubElement(key_cell, "mxGeometry", **{"height": "30", "width": "50", "as": "geometry"})
    
    val_cell = ET.Element("mxCell", id=f"{row_id}-val", parent=row_id, style=style_val, value=label, vertex="1")
    ET.SubElement(val_cell, "mxGeometry", **{"height": "30", "width": "130", "x": "50", "as": "geometry"})
    
    return row, key_cell, val_cell

def create_table(table_id, name, x, y, width, height):
    style = "shape=table;startSize=30;container=1;collapsible=1;childLayout=tableLayout;fixedRows=1;rowLines=0;fontStyle=1;align=center;resizeLast=1;"
    table = ET.Element("mxCell", id=table_id, parent="1", style=style, value=name, vertex="1")
    ET.SubElement(table, "mxGeometry", **{"height": str(height), "width": str(width), "x": str(x), "y": str(y), "as": "geometry"})
    return table

def create_relationship(edge_id, source_id, target_id, label):
    style = "edgeStyle=entityRelationEdgeStyle;endArrow=ERzeroToMany;startArrow=ERone;endFill=1;startFill=0;"
    edge = ET.Element("mxCell", id=edge_id, edge="1", parent="1", source=source_id, target=target_id, style=style, value="")
    ET.SubElement(edge, "mxGeometry", relative="1", **{"as": "geometry"})
    
    label_cell = ET.Element("mxCell", id=f"{edge_id}-label", parent=edge_id, style="edgeLabel;html=1;align=center;verticalAlign=middle;resizable=0;points=[];", value=label, vertex="1")
    ET.SubElement(label_cell, "mxGeometry", relative="1", **{"as": "geometry"})
    
    return edge, label_cell

try:
    tree = ET.parse(file_path)
    root = tree.getroot()
    mx_model_root = root.find(".//root")
    
    if mx_model_root is None:
        print("Could not find root in XML model")
        exit(1)
        
    # 1. Update User Table height (id: dAhemJaqEyVSEdy-pGvr-17)
    user_table = None
    for cell in mx_model_root.findall("mxCell"):
        if cell.get("id") == "dAhemJaqEyVSEdy-pGvr-17":
            user_table = cell
            break
            
    if user_table is not None:
        geo = user_table.find("mxGeometry")
        if geo is not None:
            geo.set("height", "480")
            print("Updated User table height to 480")
            
        # Add is_onboarded row at y=450 inside User table
        row_id = "new-user-row-is_onboarded"
        style_row = "shape=tableRow;horizontal=0;startSize=0;swimlaneHead=0;swimlaneBody=0;fillColor=none;collapsible=0;dropTarget=0;points=[[0,0.5],[1,0.5]];portConstraint=eastwest;top=0;left=0;right=0;bottom=0;"
        row = ET.Element("mxCell", id=row_id, parent="dAhemJaqEyVSEdy-pGvr-17", style=style_row, value="", vertex="1")
        ET.SubElement(row, "mxGeometry", **{"height": "30", "width": "200", "y": "450", "as": "geometry"})
        
        key_cell = ET.Element("mxCell", id=f"{row_id}-key", parent=row_id, style="shape=partialRectangle;overflow=hidden;connectable=0;fillColor=none;top=0;left=0;bottom=0;right=0;fontStyle=1;", value="", vertex="1")
        ET.SubElement(key_cell, "mxGeometry", **{"height": "30", "width": "30", "as": "geometry"})
        
        val_cell = ET.Element("mxCell", id=f"{row_id}-val", parent=row_id, style="shape=partialRectangle;overflow=hidden;connectable=0;fillColor=none;top=0;left=0;bottom=0;right=0;align=left;spacingLeft=6;", value="is_onboarded : boolean", vertex="1")
        ET.SubElement(val_cell, "mxGeometry", **{"height": "30", "width": "170", "x": "30", "as": "geometry"})
        
        mx_model_root.append(row)
        mx_model_root.append(key_cell)
        mx_model_root.append(val_cell)
        print("Added is_onboarded to User table")
        
    # 2. Add New Tables
    new_elements = []
    
    # 2.1 Playlist Table (x=40, y=230, w=180, h=150)
    new_elements.append(create_table("tbl-playlist", "Playlist", 40, 230, 180, 150))
    # Rows
    rows = [
        ("row-pl-id", "id : integer", True, False),
        ("row-pl-name", "name : string", False, False),
        ("row-pl-desc", "description : text", False, False),
        ("row-pl-img", "cover_image : string", False, False)
    ]
    for idx, (rid, label, is_pk, is_fk) in enumerate(rows):
        y = 30 + idx * 30
        r, k, v = create_table_row(rid, "tbl-playlist", y, label, is_pk, is_fk)
        new_elements.extend([r, k, v])
        
    # 2.2 PlaylistCollaborator Table (x=770, y=40, w=180, h=180)
    new_elements.append(create_table("tbl-pl-collab", "PlaylistCollaborator", 770, 40, 180, 180))
    rows = [
        ("row-pc-id", "id : integer", True, False),
        ("row-pc-plid", "playlist_id : integer", False, True),
        ("row-pc-uid", "user_id : integer", False, True),
        ("row-pc-role", "role : enum", False, False),
        ("row-pc-status", "status : enum", False, False)
    ]
    for idx, (rid, label, is_pk, is_fk) in enumerate(rows):
        y = 30 + idx * 30
        r, k, v = create_table_row(rid, "tbl-pl-collab", y, label, is_pk, is_fk)
        new_elements.extend([r, k, v])

    # 2.3 PlaylistSong Table (x=770, y=260, w=180, h=150)
    new_elements.append(create_table("tbl-pl-song", "PlaylistSong", 770, 260, 180, 150))
    rows = [
        ("row-ps-id", "id : integer", True, False),
        ("row-ps-plid", "playlist_id : integer", False, True),
        ("row-ps-sid", "song_id : string", False, True),
        ("row-ps-uid", "added_by_user_id : integer", False, True)
    ]
    for idx, (rid, label, is_pk, is_fk) in enumerate(rows):
        y = 30 + idx * 30
        r, k, v = create_table_row(rid, "tbl-pl-song", y, label, is_pk, is_fk)
        new_elements.extend([r, k, v])

    # 2.4 UserShelfSong Table (x=770, y=450, w=180, h=150)
    new_elements.append(create_table("tbl-shelf", "UserShelfSong", 770, 450, 180, 150))
    rows = [
        ("row-us-id", "id : integer", True, False),
        ("row-us-uid", "user_id : integer", False, True),
        ("row-us-sid", "song_id : string", False, True),
        ("row-us-pos", "position : integer", False, False)
    ]
    for idx, (rid, label, is_pk, is_fk) in enumerate(rows):
        y = 30 + idx * 30
        r, k, v = create_table_row(rid, "tbl-shelf", y, label, is_pk, is_fk)
        new_elements.extend([r, k, v])

    # 2.5 SongInteraction Table (x=770, y=640, w=180, h=150)
    new_elements.append(create_table("tbl-interaction", "SongInteraction", 770, 640, 180, 150))
    rows = [
        ("row-si-id", "id : integer", True, False),
        ("row-si-uid", "user_id : integer", False, True),
        ("row-si-sid", "song_id : integer", False, True),
        ("row-si-type", "type : enum", False, False)
    ]
    for idx, (rid, label, is_pk, is_fk) in enumerate(rows):
        y = 30 + idx * 30
        r, k, v = create_table_row(rid, "tbl-interaction", y, label, is_pk, is_fk)
        new_elements.extend([r, k, v])

    # Add all new tables & rows to model root
    for el in new_elements:
        mx_model_root.append(el)
        
    # 3. Add Relationships
    relationships = [
        # Playlists
        ("rel-pl-pc", "tbl-playlist", "tbl-pl-collab", "Has collaborator"),
        ("rel-u-pc", "dAhemJaqEyVSEdy-pGvr-17", "tbl-pl-collab", "Collaborates"),
        ("rel-pl-ps", "tbl-playlist", "tbl-pl-song", "Contains"),
        ("rel-u-ps", "dAhemJaqEyVSEdy-pGvr-17", "tbl-pl-song", "Adds song to"),
        # Song Interactions
        ("rel-u-si", "dAhemJaqEyVSEdy-pGvr-17", "tbl-interaction", "Interacts"),
        ("rel-s-si", "dAhemJaqEyVSEdy-pGvr-261", "tbl-interaction", "Receives interaction"),
        # Shelf Songs
        ("rel-u-uss", "dAhemJaqEyVSEdy-pGvr-17", "tbl-shelf", "Has on shelf"),
    ]
    
    for rid, src, tgt, lbl in relationships:
        e, l = create_relationship(rid, src, tgt, lbl)
        mx_model_root.append(e)
        mx_model_root.append(l)
        print(f"Added relationship: {lbl}")
        
    # Save output
    tree.write(output_path, encoding="utf-8", xml_declaration=True)
    print("Successfully updated Draw.io file!")
except Exception as e:
    print("Error:", e)
    exit(1)
