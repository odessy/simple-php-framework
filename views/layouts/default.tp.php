<!doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>

	<link rel="stylesheet" type="text/css" href="/spf/static/css/normalize.css" />
	<link rel="stylesheet" type="text/css" href="/spf/static/css/default.css" />
	<?php
	foreach ($data['stylesheets'] as $stylesheet)
	echo '<link rel="stylesheet" type="text/css" href="' . $stylesheet . '" />' . PHP_EOL;;
	?>

	<script src="/spf/static/js/jquery-1.8.3.min.js"></script>
	<?php
	foreach ($data['javascripts'] as $script)
	echo '<script src="' . $script . '"></script>' . PHP_EOL;;
	?>
</head>

<body>
	<div>
		<?php echo $data['content']; ?>
	</div>
</body>
</html>