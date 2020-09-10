<?php
defined('BASEPATH') or die ('No direct script access allowed');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- <link rel="icon" href="/favicon.ico" type="image/x-icon" /> -->
<!-- <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> -->
<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
<?php
show_stamped_CSS("/css/portal.css");
show_stamped_JS("/jscore/portal.js");
?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
<title><?php if (isset($title)) echo $title." - "; echo 'Портал СВАН' ?></title>
</head>
<body>
<?php if (isset($promed_login) && $promed_login) { ?>
<noscript><div class="minimal" id="js-warning"><h1>Внимание!</h1><h2>Для работы с системой необходим браузер с поддержкой Javascript</h2></div></noscript>
<div class="minimal">
	<img src="/img/portal/promed-title.jpg" alt="ПроМед" width="212" height="105" />
	<form id="auth_form" onsubmit="return false;" method="post" action="/?c=promed">
		<br/>
		<label for="promed-login">Имя пользователя:</label><br/>
		<input name="promed-login" id="promed-login" type="text" /><br/><br/>
		<label for="promed-password">Пароль:</label><br/>
		<input name="promed-password" id="promed-password" type="password" /><br/>
		<span style="color:red" id="login-message"></span><br/>
		<input id="auth_submit" type="submit" value="Вход" onclick="this.disabled=true; checkPOSTauth(); return false;"/>
		<br/>
	</form>
	<br/>
	<a href="/forum">Форум поддержки</a><br/>
	<a href="/">Портал СВАН</a>
</div>
<?php } else { ?>
<div id="page">
<table class="columns">
<tr><td id="portal-logo">
    <a href="/?c=portal"><img src="/img/portal/swan-logo.jpg" alt="СВАН" /></a>
</td> <!-- /header column0 -->
<td class="column1">
<?php if ( !isset($user->login) ) { ?>
<div class="block" id="auth">
  <table>
	<tr><td class="gray-tl"></td><td class="gray-t"><div class="block-title">Вход в систему</div></td><td class="gray-tr"></td></tr>
	<tr><td class="gray-l"></td>
	<td class="block-wrap">
		<div class="block-content">
			<table>
				<form id="auth_form" onsubmit="return false;" method="post" action="/?c=promed">
				<tr><td class="label">Пользователь:</td><td class="field"><input id="promed-login" name="promed-login" type="text" /></td><td></td></tr>
				<tr><td class="label">Пароль:</td><td class="field"><input id="promed-password" name="promed-password" type="password" onkeypress="checkCaps(event)" /></td>
				<td class="field"><input id="auth_submit" name="auth_submit" class="button" onclick="this.disabled=true; checkPOSTauth(); return false;" type="submit" value="Вход"/><?php if ( defined("CARDREADER_IS_ENABLE") && CARDREADER_IS_ENABLE === true ) {?><input id="card_auth_submit" name="card_auth_submit" class="button" onclick="this.disabled=true; checkPOSTcardauth(); return false;" type="button" value="Вход по карте"/></td> <?php } ?> </tr>
				</form>
			</table>
			<span id="login-message" style="color:#666666">Имя и пароль пользователя системы.</span><br />
		</div>
	</td>
	<td class="gray-r"></td></tr>
	<tr><td class="gray-bl"></td><td class="gray-b"></td><td class="gray-br"></td></tr>
  </table>
</div>
<?php } else { ?>
<div class="block" id="user">
  <table>
	<tr><td class="gray-tl"></td><td class="gray-t"><div class="block-title">Пользователь</div></td><td class="gray-tr"></td></tr>
	<tr><td class="gray-l"></td>
	<td class="block-wrap">
		<div class="block-content">
			<table>
				<tr><td>Пользователь:</td><td><?php echo $user->login ?></td><td><a class="button" href="/?c=main&m=Logout">Выход</a></td></tr>
			</table>
		</div>
	</td>
	<td class="gray-r"></td></tr>
	<tr><td class="gray-bl"></td><td class="gray-b"></td><td class="gray-br"></td></tr>
  </table>
</div>
<?php } ?>
</td> <!-- /header column1 -->
</tr>
<tr><td class="column0">
<?php foreach ($menu as $block) { ?>
<div class="block" id="<?php echo $block->name ?>">
	<table cellpadding="0" cellspacing="0">
		<tr><td class="gray-tl"></td><td class="gray-t"><div class="block-title"><?php echo $block->text ?></div></td><td class="gray-tr"></td></tr>
		<tr><td class="gray-l"></td>
            <td class="block-wrap" style="background:url(<?php echo $block->icon ?>) no-repeat">
			<div class="block-content">
				<ul class="left-menu">
<?php
foreach ($block->links as $link) {
	echo '<li><a href="'.$link->href.'"';
	if (! empty($link->description))
		echo ' title="'.$link->description.'">';
	else echo '>';
	echo $link->text."</a></li>\n";
	
	if (isset($link->show_description) && $link->show_description) {
		echo "<li class=\"description\">".$link->description."</li>\n";
	}
}
?>					
				</ul>
			</div>
		</td>
		<td class="gray-r"></td></tr>
		<tr><td class="gray-bl"></td><td class="gray-b"></td><td class="gray-br"></td></tr>
	</table>
</div>
<?php } ?>
</td> <!-- /content column0 -->
<td class="column1">

<?php if(isset($error_msg)) { ?>
  <h1><?php echo $error_msg ?></h1>
<?php } ?>

<?php if($view == "view_entry" && !empty($entry)) { ?>
<!-- Entry -->
<div class="block" id="entry">
  <table cellspacing="0">
	<tr><td class="gray-tl"></td><td class="gray-t"><div class="block-title">Новости</div></td><td class="gray-tr"></td></tr>
	<tr><td class="gray-l"></td>
	<td class="block-wrap">
		<div class="block-content">
			<div class="entry">
				<h1><?php echo $entry->title ?></h1>
				<div class="date"><?php echo $entry->created->format('d/m/Y G:i') ?></div>
				<div class="body">
<?php echo $entry->body."\n"; ?>
				</div>
			</div> <!-- /entry -->
		</div>
	</td>
	<td class="gray-r"></td></tr>
	<tr><td class="gray-bl"></td><td class="gray-b"></td><td class="gray-br"></td></tr>
  </table>
</div>
<!-- /entry -->
<?php } ?>

<?php if($view == "edit_entry") { 
  $active_checked = ($entry->active) ? 'checked' : '';
?>
<!-- Entry -->
<div class="block" id="entry">
  <table cellspacing="0">
	<tr><td class="gray-tl"></td><td class="gray-t"><div class="block-title">Новости</div></td><td class="gray-tr"></td></tr>
	<tr><td class="gray-l"></td>
	<td class="block-wrap">
		<div class="block-content">
<form method="post" enctype="multipart/form-data" action="">
	<input type="hidden" name="entry[id]" value="<?php echo $id ?>" />
	<label for="entry_title">Заголовок:</label>
	<br />
	<input id="entry_title" name="entry[title]" value="<?php echo $entry->title ?>" />
	<br /><br />
	<label for="entry_body">Сообщение:</label>
	<br />
    <textarea cols="30" rows="30" id="entry_body" name="entry[body]"><?php echo $entry->body ?></textarea>
	<br /><br />
    <select name="entry[entry_type]">
<?php foreach ($entry_types as $entry_type) { ?>
        <option value="<?php echo $entry_type->type_id ?>"><?php echo $entry_type->name.' - '.$entry_type->description ?></option>
<?php }?>
    </select>
	<br /><br />
	<label for="client_types">Показывать для:</label>
	<br />
	<div class="selector">
		<input type="checkbox" value="1" checked="checked" disabled="true" /><label>&nbsp;Localhost - всегда включен</label><br />
<?php foreach ($client_types as $client_type) {
	$client_checked = in_array($client_type->client_id, (array) $entry->client_types) ? 'checked="checked" ' : '';
?>
	<input type="checkbox" id="client_types_<?php echo $client_type->client_id ?>" name="client_types[]" value="<?php echo $client_type->client_id ?>" <?php echo $client_checked ?>/>
	<label for="client_types_<?php echo $client_type->client_id ?>"><?php echo $client_type->description ?></label><br />
<?php } ?>
	</div>
	<br />
	<div style="float:left"><input type="submit" class="button" value="Сохранить" /></div><div style="float:left; padding-left:2em ">
	<input type="checkbox" id="entry_active" name="entry[active]" value="1" checked="<?php echo $active_checked ?>" /><label for="entry_active">&nbsp;Активно</label></div>
	</form>
		</div>
	</td>
	<td class="gray-r"></td></tr>
	<tr><td class="gray-bl"></td><td class="gray-b"></td><td class="gray-br"></td></tr>
  </table>
</div>
<!-- /entry -->
<?php } ?>

<?php if($view == "news") { ?>
<!-- News -->
<div class="block" id="news">
  <table cellspacing="0">
	<tr><td class="gray-tl"></td><td class="gray-t"><div class="block-title">Новости</div></td><td class="gray-tr"></td></tr>
	<tr><td class="gray-l"></td>
	<td class="block-wrap">
		<div class="block-content">
<?php foreach($entries as $entry) { ?>			
			<div class="entry">
                <h1 <?php if (! $entry->active) echo 'style="color:#999"' ?>><?php echo $entry->title ?></h1>
				<?php if ($user->admin) { ?><div class="entry_options"><a href="/?c=portal&m=entry&action=edit&id=<?php echo $entry->id ?>">Редактировать</a></div><?php } ?>
				<div class="date"><?php echo $entry->created->format('d/m/Y G:i') ?></div>
				<div class="body">
<?php echo $entry->body."\n"; ?>
				</div>
			</div> <!-- /entry -->
<?php } ?>			
		</div>
	</td>
	<td class="gray-r"></td></tr>
	<tr><td class="gray-bl"></td><td class="gray-b"></td><td class="gray-br"></td></tr>
  </table>
</div>
<!-- /news -->
<?php } ?>
</td><!-- /content column1 -->
</tr>
<tr><td colspan=2><div id="copyright">&copy;&nbsp;2009-2011,&nbsp;ООО&nbsp;&laquo;СВАН&raquo;</div></td></tr>
</table>
</div> <!-- /page -->
<?php } ?>
</body>
<?php
echo "<!--\n";
echo "benchmark: ".$this->benchmark->elapsed_time()." s\n";
echo "mem usage: ".$this->benchmark->memory_usage()."\n";
echo "-->\n";
?>
</html>