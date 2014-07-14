<?php PHP_SAPI == 'cli' or die();

// Script time.
$start = microtime(TRUE);

//*** Basic setting up ***//

// Require settings file.
if (!file_exists("settings.php")) {
	l("FATAL: file 'settings.php' not found.");
	die();
}

require_once("settings.php");

// Require ImageMagick PHP libraries.
if (!class_exists("Imagick")) {
	l("FATAL: You need to install PHP ImageMacick support.");
	die();
}

// Create the thumbnail root folder, no matter what.
if (!is_dir(THUMBNAIL_FOLDER)) {
	if (!mkdir(THUMBNAIL_FOLDER)) {
		l("FATAL: couldn't create thumbnail root directory to '" . THUMBNAIL_FOLDER . "'.");
		die();
	}
}


//*** Gather parameters and run gerallery ***//

// Force flag. Default is false.
$force = FALSE;

// Recursive flag. Default is true.
$recursive = TRUE;

// Get params.
if (!empty($_SERVER['argv']) && count($_SERVER['argv'] > 1)) {
	// Unset the first since it's the script name.
	unset($_SERVER['argv'][0]);
	
	foreach ($_SERVER['argv'] as $argument) {
		switch ($argument) {
			case "--force":
			case "-f":
				$force = TRUE;
			break;
			case "--no-recursion";
				$recursive = FALSE;
			break;
			case "--help":
			case "-h":
				echo "gerallery generation script\n";
				echo "--force|-f, --no-recursion, --help|-h\n";
				die();
			break;
		}
	}
}

// Here we go!
generateGallery(GALLERY_PATH, $recursive, $force);

// Get end time.
$end = microtime(TRUE);

l("Gallery generation took " . round($end - $start) . " seconds.");
l("Bye.");

//*** Functions ***//

/**
 * Generates the actual gallery. A recursive function that is called for each subfolder in the gallery path if recursive flag is on.
 * @param String $private_folder The folder on the file system which consists of image files.
 * @param Boolean $recursive If we also want to generate galleries for subfolders.
 * @param Boolean $force Force gallery generation regardless if the image and the index files are already present. Useful when replacing images with the same name or removing them.
 */

function generateGallery($private_folder, $recursive = FALSE, $force = FALSE) {
	// Get public folder. Public folder is the folder that is shown to the Internets.
	$public_folder = getPublicPath($private_folder);
	
	// Subfolders.
	$subfolders = NULL;
	
	// Amount of thumbnails generated this run. For internal use and user convinience.
	$thumbnails_generated = 0;
	
	// Variables for the template. All of them are prefixed with _ and are not read in this file at all.
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
	
	// Only proceed with image processing if we have images to process.
	if ($image_files) {
		// Generate the thumbnail subfolder if it doesn't exist.
		if ($private_folder && !file_exists(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder)) {
			l("Generating thumbnail folder '" . GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "'.");
			
			// Also add permissions.
			if (!mkdir(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder, 0755, TRUE)) {
				l("FATAL: can't create thumbnail subfolder for '" . GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "'.");
				die();
			}
		}
		
		// Loop images.
		foreach ($image_files as $image_file) {
			// Check first if we even need to process this image.
			if (file_exists(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "/" . end(explode("/", $image_file))) && !$force) {
				l("Thumbnail already exists, skipping.");
				continue;
			}

			// Create ImageMagick object.
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

			// Copied from PHP.net.
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
			
			// Adjust file rights.
			@chmod(GALLERY_PATH . "/" . THUMBNAIL_FOLDER . "/" . $public_folder . "/" . $image_filename, 0644);
			
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
		require("themes" . "/" . THEME_FOLDER . "/" . "index.tpl.php");
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
			generateGallery($subfolder, $recursive, $force);
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


/**
 * Simple output function.
 * @param String $thing The thing that is being logged. Usually the message but can be an array.
 */

function l($thing) {
	echo date("c") . ": ";
	
	if (is_array($thing)) {
		print_r($thing);
	}
	else {
		echo  $thing . "\n";
	}
}