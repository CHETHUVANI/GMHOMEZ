GM HOMEZ – Patch Files
==========================

What’s inside
-------------
- config.php
- lib/render.php                (renders property cards + team rows)
- admin/login.php, _auth.php, properties.php, team.php
- patch.css                     (center "View Details" modal + gradients)
- patch.js                      (fixes Login/Signup double modal + image paths)
- assets/back.png               (placeholder background)
- data/.gitkeep, uploads/.gitkeep

How to install (XAMPP)
----------------------
1) Extract the contents of this zip into **C:\xampp\htdocs\gm-homez\\**  
   Your structure should become:
   - C:\xampp\htdocs\gm-homez\config.php
   - C:\xampp\htdocs\gm-homez\lib\render.php
   - C:\xampp\htdocs\gm-homez\admin\login.php (…and other admin files)
   - C:\xampp\htdocs\gm-homez\patch.css
   - C:\xampp\htdocs\gm-homez\patch.js

2) Open your **index.php** and add these two lines near the end of <body> (before closing </body>):
   ```html
   <link rel="stylesheet" href="patch.css">
   <script src="patch.js"></script>
   ```

3) Make sure folders exist and are writable:
   - C:\xampp\htdocs\gm-homez\uploads\
   - C:\xampp\htdocs\gm-homez\data\

4) Open:
   - http://localhost/gm-homez/admin/login.php  (login: admin / admin123)
   - Add a property with an image.
   - Then visit http://localhost/gm-homez/ and check:
     - Card images render
     - "View Details" opens centered
     - Login / Sign Up open their own modals (no extra white lead box)

Notes
-----
- If any links were hard-coded like `/gm-homez/...`, change them to `<?= $BASE ?>/...`.
- If images still don’t show, confirm that the file name in **data/properties.json**
  matches a file inside **/uploads/** exactly.