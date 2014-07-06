<!DOCTYPE html>
	<head>
			<title>index of <?php echo $_public_folder;?></title>
			<meta name="robots" content="ALL" />
			<meta content='width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;' name='viewport' />
			<meta http-equiv="last-modified" content="<?php echo date('r')?>" />
			<link href="<?php echo STATIC_BASE_URL . "/css/gallery.css"?>" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<nav>
			<ul>
				<li class="folder up">
					<a href="../">up</a>
				</li>
				<?php if (!empty($_links['paths'])):?>
				<?php foreach ($_links['paths'] as $path):?>
				<li class="folder">
					<a href="<?php echo $path['link'];?>"><?php echo $path['folder']?></a>
				</li>
				<?php endforeach;?>
				<?php endif;?>
			</ul>
			<p class="image-count"><?php echo ((!empty($_images)) ? count($_images) : 0)?> images</p>
		</nav>
		<?php if (!empty($_images)):?>
		<article>
		<?php foreach ($_images as $_image):?>
			<a href="<?php echo $_image['full_url'];?>" class="image">
				<img src="<?php echo $_image['thumbnail_url'];?>" alt="" width="<?php echo $_image['thumb_width']?>" height="<?php echo $_image['thumb_height']?>"/>
			</a>
		<?php endforeach;?>
		</article>
		<?php endif;?>
	</body>
</html>