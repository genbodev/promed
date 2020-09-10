<?php if (defined("MARSH_IS_ENABLE") && MARSH_IS_ENABLE === true) { ?>
	<applet 
		name = "marsh"
		archive = "applets/marsh.jar"
		code = "ru/swan/applet/SerialNumber"
		width = 0
		height = 0
		style="width:0px; height:0px"
		>
        <param name="initial_focus" value="false">
	</applet>
<?php } ?>
<div class="login-form">
	<?php if (isset($login_warning)) { ?>
	<div class="notices" style="padding-top: 0;">
		<div style="width: 400px">
			<div style="margin-bottom: 0.5em">
				<!--Время ожидания истекло. Авторизуйтесь в системе заново.-->
				<?php
					echo $timeout_msg;
				?>
			</div>
		</div>
	</div>
	<br />
	<?php } ?>
	<?php if (isset($login_error)) { ?>
	<div class="notices" style="padding-top: 0;">
		<div style="width: 400px">
			<div style="margin-bottom: 0.5em">
				<!--Время ожидания истекло. Авторизуйтесь в системе заново.-->
				<?php
					echo $login_error;
				?>
			</div>
		</div>
	</div>
	<br />
	<?php } ?>
	<h1><?php echo $titles['auth_page_enter']?></h1>
	<?php 
		$cfg = $this->config->item('portal');
		if (!empty($cfg['phones'])) { ?>
		<div class="support">
			<?php
				if (getRegionNick() == 'kz') {
					echo '<h3 class="first-child"><img width="20" src="/img/portal/phone-icon.jpg" />&nbsp;Call-центр</h3>';
					echo '<h3 class="last-child">' . $kz_label2 . '</h3>';

					$phones = $cfg['phones'];
					foreach ($phones as $key) {
						echo '<h3 class="first-child">'.$key.'</h3>';
					}

					echo "<br>";
					echo '<h3 class="first-child">' . $kz_label1 . '</h3>';
				} else if(getRegionNick() == 'krym'){
					echo '<h3 class="first-child"><img width="20" src="/img/portal/phone-icon.jpg" />&nbsp;Call-центр</h3>';
					echo '<h3 class="last-child">' . $cfg['workTime'] . '</h3>';

					$phones = $cfg['phones'];
					foreach ($phones as $key) {
						echo '<h3 class="first-child">'.$key.'</h3>';
					}

					echo "<br>";
					echo '<h3 class="first-child"><a href="mailto:' . $cfg['mail'] . '">' . $cfg['mail'] . '</a></h3>';
				} else {
					echo '<h3 class="first-child">' . $phones_label_1 . '</h3>';
					echo '<h3 class="first-child">' . $phones_label_2 . '</h3>';
					echo '<h3 class="last-child"">' . $phones_label_3 . '</h3>';

					$phones = $cfg['phones'];
					foreach ($phones as $key) {
						echo '<p>'.$key.'</p>';
					}
				}
			?>
		</div>
	<?php } ?>
	<form id="auth_form" onsubmit="return false;" method="post" action="/?c=promed">
		<?php
			if ( getenv('USER_CAN_CHANGE_REGION') ) {
		?>
				<label>Регион</label><br />
				<select id="promed-region">
				<?php
					$region = getenv('REGION');
					foreach($this->config->item('regions') as $number => $one) {
						$selected = "";
						if ($one['nick'] == $region) {
							$selected = "selected"; // по умолчанию пусть выбрано то что задано в конфиге.
						}
						echo "<option {$selected} value='{$one['nick']}'>{$number}. {$one['name']}</option>";
					}
				?>
				</select>
				<br />

				<label>Тип БД</label><br />
				<select id="promed-dbtype">
				<?php
					$dbtype = getenv('DBTYPE');
					foreach($this->config->item('dbtypes') as $number => $one) {
						$selected = "";
						if ($one['nick'] == $dbtype) {
							$selected = "selected"; // по умолчанию пусть выбрано то что задано в конфиге.
						}
						echo "<option {$selected} value='{$one['nick']}'>{$number}. {$one['name']}</option>";
					}
				?>
				</select>
				<br />
		<?php
			}

			$onlyByEsia = false;
			if (isset($esia_config['enabled']) && $esia_config['enabled'] === true) {
				if (isset($options['globals']['use_esia_only']) && $options['globals']['use_esia_only'] == 1) {
					$onlyByEsia = true;
				}
		?>
		<a href="/?c=<?php if (!empty($esia_config['type']) && $esia_config['type'] == 'egisz') { echo 'IaEgisz'; } else { echo 'Esia'; } ?>&m=login">Войти через <?php if (!empty($esia_config['type']) && $esia_config['type'] == 'egisz') { echo 'ИА ЕГИСЗ'; } else { echo 'портал Госуслуг РФ (ЕСИА)'; } ?></a><br><br>
		<?php
			}
		?>
		<div style="<?php echo $onlyByEsia ? 'display:none;' : ''; ?>">
		<?php
			echo '<label>'.$username_label.'</label><br />';
		?>
		<!--<label>Имя пользователя</label><br />-->
		<input onkeydown="auth1keydown(event);" type="text" id="promed-login" name="promed-login" />
		<br />
		<?php
			echo '<label>'.$password_label.'</label><br />';
		?>
		<!--<label>Пароль</label><br />-->
		<input onkeydown="auth1keydown(event);" type="password" id="promed-password" name="promed-password" onkeypress="checkCaps(event)" />
		<br />
		<div id="changepassword" style="display:none;">
		<!--<label>Новый пароль</label><br />-->
		<?php
			echo '<label>'.$new_pass.'</label><br />';
		?>
		<input onkeydown="auth1keydown(event);" onkeyup="checkPasswordFields()" type="password" id="promed-new-password" name="promed-new-password" onkeypress="checkCaps(event)" />
		<br />
		<!--<label>Повторите пароль</label><br />-->
		<?php
			echo '<label>'.$repeat_pass.'</label><br />';
		?>
		<input onkeydown="auth1keydown(event);" onkeyup="checkPasswordFields()" type="password" id="promed-new-password-two" name="promed-new-password-two" onkeypress="checkCaps(event)" />
		<br />
		</div>
		<?php
			echo '<button type="submit" id="auth_submit" name="auth_submit" onclick="checkPOSTauth();">'.$auth_label.'</button>';

		?>
		<!--<button type="submit" id="auth_submit" name="auth_submit" onclick="checkPOSTauth();">Войти в систему</button>-->
		<span id="login-message" style="color:#666666"></span>
		</div>
	</form>
	<?php if (defined("CARDREADER_IS_ENABLE") && CARDREADER_IS_ENABLE === true) { ?>
		<div style="<?php echo $onlyByEsia ? 'display:none;' : ''; ?>">
		<div>
		<label><?php echo lang('Typ_tokena') ?>:</label><br />
			<select id="promed-tokentype" onchange="checkTokenType();">
				<?php if (getRegionNick() == 'kz') { ?>
				<option selected value='nca1'>NCALayer - PKCS#12</option>
				<option value='nca2'>NCALayer - Kaztoken</option>
				<?php } ?>
				<option <?php if (getRegionNick() == 'kz') { echo ''; } else { echo 'selected';} ?> value='aa1'>AuthApi - eToken ГОСТ</option>
				<option value='aa2'>AuthApi - JaCarta ГОСТ</option>
				<option value='aa3'>AuthApi - Рутокен</option>
				<option value='aae1'>AuthApi (TomEE) - JaCarta</option>
				<option value='aae2'>AuthApi (TomEE) - Рутокен</option>
				<!--<option value='aa4'>AuthApi - CSP</option>-->
				<option value='cc'>КриптоПро ЭЦП Browser plug-in</option>
				<option value='vn'>ViPNet PKI Client (Web Unit)</option>
				<option value='5'>AuthApplet - eToken ГОСТ</option>
				<option value='8'>AuthApplet - Lissi</option>
				<!--<option value='9'>AuthApplet - eToken Pro</option>
				<option value='10'>AuthApplet - jaCarta</option>
				<option value='11'>AuthApplet - Kaztoken</option>
				<option value='12'>AuthApplet - PKCS#12 ГОСТ</option>
				<option value='13'>AuthApplet - PKCS#12 RSA</option>-->
			</select>
		</div>
		<div>
		<div id="pin-div">
			<label><?php if (getRegionNick() == 'kz') { echo lang('Parol').':'; } else { echo 'ПИН-код:';} ?></label><br />
			<input onkeydown="auth2keydown(event, '<?php echo $ecp_message; ?>');" type="password" id="promed-pincode" name="promed-pincode"><br />
		</div>
		<div id="cert-div" style="display: none;">
			<span id="PlugInEnabledTxt">Плагин не загружен</span>
			<img src="img/red_dot.png" width="10" height="10" id="PluginEnabledImg"/><br />
			<label>Сертификат:</label><br />
			<select size="4" id="CertListBox" name="CertListBox" style="width:100%;resize:none;"></select><br />
		</div>
		</div>
		<button type="button" id="card_auth_submit" name="card_auth_submit" onclick="this.disabled=true; checkPOSTcardauth('<?php echo $ecp_message; ?>'); return false;">
			<?php
				echo $authcard_label;
			?>
		</button>
		<span id="card-login-message" style="color:#666666"></span><br />
		</div>
	<?php } ?>
</div>

<script type="text/javascript">
	window.onload = function() {
		document.cardtype = '';
		document.getElementById('promed-login').focus();
<?php if (defined("CARDREADER_IS_ENABLE") && CARDREADER_IS_ENABLE === true) { ?>
		promedCardAPI.checkProMedPlugin();
<?php } ?>

<?php if (defined("MARSH_IS_ENABLE") && MARSH_IS_ENABLE === true) { ?>
		// будем пытаться залогиниться с использованием МАРША.
		var respMarshLogin = loginWithMarsh();
		log(respMarshLogin);
<?php } ?>
	}

	var password_minlength = <?php echo !empty($options['globals']['password_minlength'])?"'".$options['globals']['password_minlength']."'":"false"; ?>;
	var password_hasuppercase = <?php echo !empty($options['globals']['password_hasuppercase'])?"'".$options['globals']['password_hasuppercase']."'":"false"; ?>;
	var password_hasnumber = <?php echo !empty($options['globals']['password_hasnumber'])?"'".$options['globals']['password_hasnumber']."'":"false"; ?>;
	var password_hasspec = <?php echo !empty($options['globals']['password_hasspec'])?"'".$options['globals']['password_hasspec']."'":"false"; ?>;
</script>