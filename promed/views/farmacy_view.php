<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
<!-- <link rel="icon" href="/favicon.ico" type="image/x-icon" /> -->
<!-- <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> -->
<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
<?php
// Списки CSS файлов и JS файлов теперь генерируются в контроллере в виде массивов 
// и передаются представлению для вывода

// CSS файлы
foreach($css_files as $css) {
	show_stamped_CSS($css);
}

// JS файлы
foreach($js_files as $js) {
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

<body onload="javascript:promedCardAPI.checkProMedPlugin()" style="color:black; background-color:#16334a;">
</body>

</head>
</html>
