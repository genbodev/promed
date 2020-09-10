<?php
defined('BASEPATH') or die ('No direct script access allowed');
if ($client_type == "unknown" || $client_type == "WAN") {
	include("minimal.php"); // минимальная форма логина без портала
	die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- <link rel="icon" href="/favicon.ico" type="image/x-icon" /> -->
<!-- <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> -->
<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
<?php
show_stamped_CSS("extjs/resources/css/ext-all.css");
//
show_stamped_CSS("css/form.css");
//<!-- Переназначение и правки стилей фреймворка -->
show_stamped_CSS("css/customext.css");
//<!-- Классы иконок -->
show_stamped_CSS("css/iconcls.css");
//<!-- Стили стартовой страницы -->
show_stamped_CSS("css/blog.css");
show_stamped_JS("jscore/const.php");
show_stamped_JS("extjs/adapter/ext/ext-base.js");
show_stamped_JS("extjs/ext-all-debug.js");
show_stamped_JS("extjs/source/locale/ext-lang-ru.js");
show_stamped_JS("/jscore/libs/ext.ux.messagewindow.js");
?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
<!--<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />-->

<style type="text/css">
.notAllowBlank
{
	border: 1px solid green
}

.hideTrigger
{
	visibility: hidden;
}
.x-form-field-wrap .x-form-search-trigger{background-image:url(../images/default/form/search-trigger.gif);cursor:pointer;}
</style>
<title><?php if (isset($title)) echo $title; else echo('Портал СВАН')?></title>
</head>
<body>
<?php
if (isset($_SERVER['HTTP_REFERER'])) {
  //echo $_SERVER['HTTP_REFERER'];
  if (isset($redir_login)) {
?>
	<script>
		new Ext.ux.window.MessageWindow({
			title: 'Авторизация',
			autoDestroy: true,//default = true
			autoHeight: true,
			autoHide: true,//default = true
			help: false,
			bodyStyle: 'text-align:center',
			closable: false,
			//pinState: null,
			//pinOnClick: false,
			hideFx: {
				delay: 5000,
				//duration: 0.25,
				mode: 'standard',//null,'standard','custom',or default ghost
				useProxy: false //default is false to hide window instead
			},
			html: '<br/><b>Для запуска системы необходимо авторизоваться.</b><br />Введите имя пользователя и пароль.<br/><br/>',
			iconCls: 'info16',
			origin: {
				pos: "t-t",//position to align to (see {@link Ext.Element#alignTo} for more details defaults to "br-br").
				offX: 0, //amount to offset horizontally (-20 by default)
				offY: 20, //amount to offset vertically (-20 by default)
				spaY: 5    //vertical spacing between adjacent messages
			},
			showFx: {
				align: 't',
				delay: 0,
				//duration: 0.5, //defaults to 1 second
				mode: 'standard',//null,'standard','custom',or default ghost
				useProxy: false //default is false to hide window instead
			},
			width: 250 //optional (can also set minWidth which = 200 by default)
		}).show(Ext.getDoc()); //show(Ext.getDoc());
	</script>
<?php
  }
}
?>

<div align="center">
<div id="header">
	<table style="width: 95%; max-width: 1300px" border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td width="8"></td>
		<td width="300"></td>
		<td width="8"></td>
		<td></td>
		<td width="8"></td>
		</tr>
		<tr>
		<td></td>
		<td valign="top" align="right">
<?php
if (!isset($user)) { // Если юзер не авторизован - выводим блок логина
	include('blog/auth.php');
	echo '</td><td></td><td align="left" valign="top">';
	include('blog/auth_tip.php');
} else {
	include('blog/user.php');
	echo '</td><td></td><td align="left" valign="top">';
	include('blog/user_tip.php');
}
?>
		<div style="float: right; text-align: center;"><a style="text-decoration:none;" href="/"><img src="/img/portal/portal-logo.gif" width="74" height="74" /><span style="font-size:24px; color:#FFF"><br />
		Портал<br />
		&laquo;СВАН&raquo;</span></a></div>
		</td>
		<td></td>
		</tr>
		<tr>
		<td colspan=5" style="height: 10px"></td>
		</tr>
		<tr>
		<td class="white-tl"></td>
		<td colspan="3" class="white-t"></td>
		<td class="white-tr"></td>
		</tr>
		<tr>
		<td class="white-l"></td>
		<td style="background-color:#FFF; text-align:right; vertical-align:top">
<?php
include ('blog/main_menu.php');
?>
		</td>
		<td bgcolor="#FFFFFF"></td>
		<td style="background-color:#FFF; text-align:left; vertical-align:top">
<?php
if ($view == 'blog') {
    foreach ($entries as $entry) {
		if ($entry->publish && $entry->publish_news) {
			if (strstr($entry->msg_groups, $client_type)) { // TODO: переделать нахер
                include ('blog/entry.php');
			}
		}
    }
}
if ($view == 'userblog') {
    foreach ($entries as $entry) {
        include ('blog/entry.php');
    }
}
if ($view == 'myblog') {
    foreach ($entries as $entry) {
    	include ('blog/entry.php');
    }
}
if ($view == 'comments') {
	include('blog/entry.php');
	foreach ($comments as $comment) {
		include ('blog/comment.php');
	}
}
if ($view == 'myblog_comments') {
	include('blog/entry.php');
	foreach ($comments as $comment) {
		include ('blog/comment.php');
	}
}
if ($view == 'add_entry_form') {
	include('blog/add_entry_form.php');
}
if ($view == 'edit_entry_form') {
	include('blog/edit_entry_form.php');
}
if (!empty($pager)) {
	echo 'Страницы: '.$pager;
}
?>
		</td>
		<td class="white-r"></td>
		</tr>
		<tr>
		<td class="white-bl"></td>
		<td colspan="3" class="white-b"></td>
		<td class="white-br"></td>
		</tr>
		<tr>
		<td></td>
		<td style="font-size:10px; text-align:left; color:#bbb"><span id="blog_view_ver"></span></td>
		<td></td>
		<td style="font-size:10px; text-align:center">&copy;&nbsp;2009-2010,&nbsp;ООО&nbsp;&laquo;СВАН&raquo;
		<div style="float:right; font-size:10px; text-align:right; color:#bbb">benchmark&nbsp;<?php echo $this->benchmark->elapsed_time();?>&nbsp;s<br />
		mem usage:&nbsp;<?php echo $this->benchmark->memory_usage();?></div>
		</td>
		<td></td>
		</tr>
	</table>
</div>
<script language="JavaScript">
document.getElementById('blog_view_ver').innerHTML='ver. '+PromedVer+' revision '+Revision+'<br />updated: '+PromedVerDate;
</script>
</body>
</html>