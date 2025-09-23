GM HOMEZ — gmhomez.in EB Patch
===============================

This zip contains ONLY the files you need to overlay on your existing project.
How to use:

1) Unzip this *over* your project root (the folder that has index.php).
   The following files/paths will be added/overwritten:
   - .htaccess
   - robots.txt
   - sitemap.php
   - uploads_check.php
   - .platform/php/php.ini
   - .platform/hooks/postdeploy/01_permissions.sh
   - seo_head_snippet.html (paste into <head> of your homepage)

2) Zip your WHOLE project (so index.php, .htaccess, .platform, assets, uploads, etc are at the top of the zip).
3) Upload & deploy the zip to your **Load-balanced** Elastic Beanstalk environment.
4) In DNS, point **www.gmhomez.in** (CNAME) to your EB CNAME. Redirect root gmhomez.in to https://www.gmhomez.in/ (or use Route 53 A (Alias)).
5) Issue an ACM cert for gmhomez.in + www.gmhomez.in in the **same region** as your EB env and attach it to the Load Balancer (443).
6) Verify:
   - https://www.gmhomez.in/ loads with green lock
   - https://www.gmhomez.in/sitemap.xml renders
   - https://www.gmhomez.in/robots.txt loads
   - https://www.gmhomez.in/uploads_check.php lists your image files
7) Paste the contents of seo_head_snippet.html into your homepage <head> (index.php).

That's it.
