<?php PHP_SAPI == 'cli' or die() ?>
<!DOCTYPE html>
	<head>
			<title>index of <?= $_public_folder;?></title>
			<meta name="robots" content="ALL" />
			<meta content='width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;' name='viewport' />
			<meta http-equiv="last-modified" content="<?= date('r')?>" />
			<link href="<?= STATIC_BASE_URL . "/themes/" . THEME_FOLDER . "/css/gallery.css"?>" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<header>
			<h1><?= $_public_folder;?></h1>
			<nav>
				<ul>
					<li class="folder up">
						<a href="../">up</a>
					</li>
					<?php if (!empty($_links['paths'])):?>
					<?php foreach ($_links['paths'] as $path):?>
					<li class="folder">
						<a href="<?= $path['link'];?>"><?= $path['folder']?></a>
					</li>
					<?php endforeach;?>
					<?php endif;?>
				</ul>
			</nav>
			<p class="image-count"><?= count($_images) == 1 ? "1 image" : count($_images) . " images";?></p>
		</header>
		<?php if (!empty($_images)):?>
		<article>
		<?php foreach ($_images as $_image):?>
			<a href="<?= $_image['full_url'];?>" class="image">
				<img src="<?= $_image['thumbnail_url'];?>" alt="" width="<?= $_image['thumb_width']?>" height="<?= $_image['thumb_height']?>"/>
			</a>
		<?php endforeach;?>
		</article>
		<?php endif;?>
	</body>
</html>