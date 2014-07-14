<?php PHP_SAPI == 'cli' or die();

require("settings.php");
@mkdir(THUMBNAIL_FOLDER);

$param = FALSE;

if (!empty($_SERVER['argv']) && !empty($_SERVER['argv'][1])) {
	l("Forcing generation.");
	$force = $_SERVER['argv'][1];
}

generateGallery(GALLERY_PATH, TRUE, $force);

function generateGallery($private_folder, $recursive = FALSE, $force = FALSE) {
	// Get public folder.
	$public_folder = getPublicPath($private_folder);
	$subfolders = NULL;
	$thumbnails_generated = 0;
	
	// Variables for the template.
	$_images = array();
	$_links = array();
	$_public_folder = $public_folder;
	
	// First find out subfolders if the recursive mode is on.  This is because not every folder might have images but their subfolders do.
	if ($recursive) {
		// Subfolders
		$subfolders = glob($private_folder . "/*", GLOB_ONLYDIR);
	}
	
	// Now on with the images.
	l("Checking folder '" . $private_folder . "' for images.");
	
	$image_files = glob($private_folder . "/{*.jpg,*.JPG,*.png,*.PNG}", GLOB_BRACE);
	
	// Only proceed with image processing if we have images.
	if ($image_files) {
		// Generate the thumbnail directory if it doesn't exist.
		if ($private_folder && !file_exists(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder)) {
			l("Generating thumbnail folder '" . GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "'.");
			mkdir(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder, 0755, TRUE);
		}
		
		foreach ($image_files as $image_file) {
			// Check first if we even need to process this image.
			if (file_exists(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "/" . end(explode("/", $image_file))) && !$force) {
				l("Thumbnail already exists, skipping.");
				continue;
			}

			$image = new Imagick($image_file);
			
			if (!$image) {
				l("Couldn't load image file '" . $image_file . "'.");
				continue;
			}
			
			$image_filename = end(explode("/", $image->getImageFilename()));
			$orientation = $image->getImageOrientation();
			$geometry = $image->getImageGeometry();
			$width = $geometry['width'];
			$height = $geometry['height'];

			switch($orientation) {
				case imagick::ORIENTATION_BOTTOMRIGHT:
					$image->rotateimage("#000", 180); // rotate 180 degrees
				break;

				case imagick::ORIENTATION_RIGHTTOP:
					$image->rotateimage("#000", 90); // rotate 90 degrees CW
				break;

				case imagick::ORIENTATION_LEFTBOTTOM:
					$image->rotateimage("#000", -90); // rotate 90 degrees CCW
				break;
			}

			$image->thumbnailImage(0, THUMBNAIL_HEIGHT);
			l("Processing image '" . $image->getImageFilename() . "'.");
						
			$image->writeImage(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "/" . $image_filename);
			
			$thumbnails_generated++;
			
			$thumbnail_geometry = $image->getImageGeometry();
			$thumbnail_width = $image->getImageWidth();
			$thumbnail_height = $image->getImageHeight();
			
			chmod(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "/" . $image_filename, 0644);
			
			// Add to template variable.
			$_images[] = array(
				'thumbnail_url' => BASE_URL . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "/" . $image_filename,
				'full_url' => BASE_URL . "/" . $public_folder . "/" . $image_filename,
				'width' => $width,
				'height' => $height,
				'thumb_width' => $thumbnail_width,
				'thumb_height' => $thumbnail_height,
			);
		}
		
		l("Generated " . $thumbnails_generated . " thumbnails.");
	}
	else {
		l("No images in " . $private_folder . ".");
	}
	
	if ($subfolders) {
		foreach ($subfolders as $subfolder) {
			$_links['paths'][] = array(
				'link' => BASE_URL . "/" . getPublicPath($subfolder),
				'folder' => end(explode("/", getPublicPath($subfolder))),
			);
		}
	}
	
	l("Processing HTML index file 'index.html'.");
	
	// Only generate the index if the file doesn't exist or we have generated some thumbnails or the force flag is on.
	if (!file_exists($private_folder . "/" . "index.html") || $thumbnails_generated || $force) {
		// Generate HTML.
		ob_start();
		require("index.tpl.php");
		$html = ob_get_contents();
		ob_end_clean();

		file_put_contents($private_folder . "/" . "index.html", $html);
	}
	else {
		l("Skipping HTML index generation, no changes required.");
	}
	
	if ($subfolders) {
		l("Found subfolders in '" . $private_folder . "'.");
		foreach ($subfolders as $subfolder) {
			generateGallery($subfolder, TRUE, $force);
		}
	}
	else {
		l("No subfolders found in '" . $private_folder . "'.");
	}
}

function getPublicPath($path) {
	if (strpos($path, GALLERY_PATH) !== FALSE) {
		$path = str_replace(GALLERY_PATH, "", $path);
		
		// Remove leading slash.
		$path = trim($path, '/');
		
		return $path;
	}
}

function l($thing) {
	echo date("c") . ": ";
	
	if (is_array($thing)) {
		print_r($thing);
	}
	else {
		echo  $thing . "\n";
	}
}