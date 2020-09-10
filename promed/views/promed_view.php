<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
	<head>
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
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
		<style>
			.notAllowBlank
			{
				border: 1px solid green
			}

			.hideTrigger
			{
				visibility: hidden;
			}
		</style>

		<style type="text/css" media="all">
			.block
			{
				position: absolute;
				width: 400px;
				height: 80px;
				left: 50%;
				top: 350px;
				margin-left: -200px;
				margin-top: 0px;
				z-index: 0
			}
		</style>

	</head>

	<body onload="javascript:promedCardAPI.checkProMedPlugin()" style="color:black; background-color:#16334a;">
		<div style="position: absolute; left: 0; bottom: 0; width: 100%; padding-bottom: 30px">
			<div style="float: right; color: #5b89a7; margin-right: 30px">
				<p style="font-family: Verdana,Geneva,sans-serif; font-size: 38px"><?php echo $promed_page; ?><span style="font-size:16px; font-style: italic;" id="promed-region"></span></p>
				<p style="font-family: Verdana,Geneva,sans-serif; font-size: 12px">
					Версия: <?php echo $PromedVer; ?><br />
					Ревизия: <?php echo $Revision; ?> (<?php echo $RevDate; ?>)
					<div style="font-family: Verdana,Geneva,sans-serif; font-size: 12px" id="promed-info"></div>
				</p>
			</div>
		</div>
	</body>

	<div class="noConnectionWarning">
		Внимание! Связь отсутствует, запись идёт локально.
	</div>

</html>
