import os

files = [
    "resources/views/welcome.blade.php",
    "resources/views/user/search-results.blade.php",
    "resources/views/shares/show.blade.php",
    "resources/views/settings/index.blade.php",
    "resources/views/profile/taste.blade.php",
    "resources/views/profile/show.blade.php",
    "resources/views/profile/saved.blade.php",
    "resources/views/profile/following.blade.php",
    "resources/views/profile/followers.blade.php",
    "resources/views/profile/edit.blade.php",
    "resources/views/discovery.blade.php",
    "resources/views/dashboard.blade.php"
]

for file_path in files:
    full_path = "c:/laragon/www/musicsocial-main/" + file_path
    if not os.path.exists(full_path): 
        print(f"File not found: {full_path}")
        continue
    
    with open(full_path, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
        
    # Replace left sidebar (change lg:col-span-from 2 to 3, and add xl:col-span-2)
    content = content.replace('class="hidden md:block md:col-span-4 lg:col-span-2"', 'class="hidden md:block md:col-span-4 lg:col-span-3 xl:col-span-2"')
    
    # Replace main content (change lg:col-span from 7 to 6, and add xl:col-span-7)
    content = content.replace('class="col-span-12 md:col-span-8 lg:col-span-7"', 'class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7"')
    content = content.replace('class="col-span-12 md:col-span-8 lg:col-span-7 ', 'class="col-span-12 md:col-span-8 lg:col-span-6 xl:col-span-7 ')
    
    # Replace right sidebar (already lg:col-span-3, we keep it as is)
    
    if content != original:
        with open(full_path, "w", encoding="utf-8") as f:
            f.write(content)
        print(f"Updated {file_path}")
    else:
        print(f"No changes needed for {file_path}")

print("Done.")
