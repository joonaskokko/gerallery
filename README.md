gerallery
=========

gerallery is a simple static PHP image gallery generator. You run the script once and gerallery will generate you thumbnails and HTML index pages. gerallery doesn't use any kind of database, hence it won't store any metadata such as titles or descriptions of images. It is just for listing images.

To run gerallery, simply first copy settings.example.php to settings.php and fill in the settings:

`define('GALLERY_PATH', '/var/www/gallery');
This is the path to the place where you store the images. The images must be accessible from the web.

`define('THUMBNAIL_HEIGHT', 200);
Thumbnail height in pixels.

`define('THUMBNAIL_FOLDER', ".thumbnails");
Thumbnail folder name. This will be placed in the GALLERY_PATH folder.

`define('BASE_URL', 'http://example.com');
The URL where the gallery is accessible.

`define('STATIC_BASE_URL', 'http://static.example.com');
URL for static file serving. This is the path where CSS files are read.

`define('THEME_FOLDER', 'default');
Theme folder name to use. This is basically 'themes/THEME_FOLDER/'. The default is 'default'.

After filling the settings.php, you can run gerallery by typing "php generate.php" in gerallery's folder. If you want to force gallery generation, add 1 or TRUE to the end ("php generate.php true").
