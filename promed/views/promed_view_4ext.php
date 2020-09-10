<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" >
	<head>
		<!--<link rel="icon" href="/favicon.ico" type="image/x-icon" /> -->
		<!--<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> -->
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link href="../../extjs4/resources/css/custom.css" media="all" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
		<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/jscore/libs/flot/excanvas.min.js"></script><![endif]-->
		<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/jscore/libs/flot/excanvas.min.js"></script><![endif]-->
		<?php
		// Списки CSS файлов и JS файлов теперь генерируются в контроллере в виде массивов
		// и передаются представлению для вывода
		// CSS файлы
		foreach ($css_files as $css) {
			show_stamped_CSS($css);
		}

		// JS файлы
		foreach ($js_files as $js) {
			show_stamped_JS($js);
		}
		?>

		<title><?php echo $promed_page_title; ?></title>

 
	</head>

	<body style="color:black; background: #c3d9ff; /* Old browsers */
		background: -moz-linear-gradient(top,  #c3d9ff 0%, #b1c8ef 41%, #98b0d9 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#c3d9ff), color-stop(41%,#b1c8ef), color-stop(100%,#98b0d9)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top,  #c3d9ff 0%,#b1c8ef 41%,#98b0d9 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top,  #c3d9ff 0%,#b1c8ef 41%,#98b0d9 100%); /* Opera 11.10+ */
		background: -ms-linear-gradient(top,  #c3d9ff 0%,#b1c8ef 41%,#98b0d9 100%); /* IE10+ */
		background: linear-gradient(to bottom,  #c3d9ff 0%,#b1c8ef 41%,#98b0d9 100%); /* W3C */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#c3d9ff', endColorstr='#98b0d9',GradientType=0 ); /* IE6-9 */
	">
	</body>

	<div class="noConnectionWarning">
		Внимание! Связь отсутствует, запись идёт локально.
	</div>

</html>
