<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>
<div class="left">
	<div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}">
		<div class="caption">
			<h2>
				Случай медицинского освидетельствования водителя №{EvnPLDispDriver_Num} от {EvnPLDispDriver_setDate}<br>
				{Lpu_Nick}
			</h2>
		</div>
		<?php
			// Кнопка отображается, если выбран случай медицинского освидетельствования водителей, связан с Талоном ЭО у которого текущий статус «Обслуживание» и Пункт обслуживания=пункту обслуживания Пользователя.
			if (
					!empty($_SESSION['CurArmType']) && $_SESSION['CurArmType'] == 'paidservice' // АРМ платных услуг
					&& $ElectronicTalonStatus_id == 3 && $ElectronicService_id == $_SESSION['CurARM']['ElectronicService_id'] // сервис текущий
					&& $ElectronicQueueInfo_IsOff != 2
			) {
		?>
		<div style='clear:both; overflow: hidden;'>
			<a class="button" onClick="getWnd('swPersonEmkWindow').finishElectronicTalon('paid', <?php echo $ElectronicTalon_id; ?>, false, <?php echo (havingGroup('DrivingCommissionReg') ? $EvnPLDispDriver_id : 'false')?>);" title="Завершить прием"><span>Завершить&nbsp;прием</span></a>
			<a class="button" onClick="getWnd('swPersonEmkWindow').finishElectronicTalonAndGoNext('paid', <?php echo $ElectronicTalon_id; ?>, <?php echo (havingGroup('DrivingCommissionReg') ?  $EvnPLDispDriver_id : 'false')?>);" title="Завершить прием и вызвать следующего"><span>Завершить&nbsp;прием&nbsp;и&nbsp;вызвать&nbsp;следующего</span></a>
		</div>
		<?php
			}
		?>
		<div class="text">
			<?php
			$is_allow_edit_head = $is_allow_edit;
			if (!havingGroup('DrivingCommissionReg') && getRegionNick() == 'perm') {
				// только регистратор может редактировать заголовок
				$is_allow_edit_head = false;
			}
			?>

			<!-- Информированное добровольное согласие -->
			<h3>Информированное добровольное согласие</h3>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид оплаты: <?php if($is_allow_edit_head) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputPayType" style='color:#000;' class="link" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit_head) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left; padding:5px 0;'>Дата подписания согласия/отказа: <?php if($is_allow_edit_head) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputConsDate" style='color:#000;' class="link" dataid='{EvnPLDispDriver_consDate}'><?php } echo empty($EvnPLDispDriver_consDate)?$empty_str:'{EvnPLDispDriver_consDate}'; if($is_allow_edit_head) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaConsDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><label style='float:left; margin: -5px 0 5px <?php echo (getRegionNumber() == 59) ? '450px' : '350px' ?>'><input id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_checkall" type="checkbox" <?php if(!$is_allow_edit_head) { echo 'disabled'; } ?> {EvnPLDispDriver_allChecked} />&nbsp;Выбрать все</label></div>
			{DopDispInfoConsent}

			<!-- Маршрутная карта -->
			<div id="EvnUslugaDispDopList_{pid}" class="data-table">
				<div class="caption">
					<h2><span id="EvnUslugaDispDopList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Маршрутная карта
			</span></h2>
				</div>
				<table id="EvnUslugaDispDopTable_{pid}" style="display: block;">
					<col width="40%" class="first" />
					<col width="10%" />
					<col width="50%" class="last" />
					<col class="toolbar"/>
					<thead>
					<tr>
						<th>Наименование осмотра / исследования</th>
						<th>Дата выполнения</th>
						<th>Назначение / направление</th>
						<th class="toolbar"></th>
					</tr>
					</thead>
					<tbody id="EvnUslugaDispDopList_{pid}">{EvnUslugaDispDop}</tbody>
				</table>
			</div>

			{EvnUslugaDispDopTemplate}
			<!-- Файлы -->
			{EvnMediaData}
			<!-- Результат -->
			<?php
				$is_allow_edit_result = $is_allow_edit;
				if (!havingGroup('DrivingCommissionTherap') && getRegionNick() == 'perm') {
					// только терапевт может редактировать результат
					$is_allow_edit_result = false;
				}
			?>
			<h3>Результат</h3>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Медицинское обследование закончено: <?php if($is_allow_edit_result) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputIsFinish" style='color:#000;' class="link" dataid='{EvnPLDispDriver_IsFinish}'><?php } echo empty($EvnPLDispDriver_IsFinish_Name)?$empty_str:'{EvnPLDispDriver_IsFinish_Name}'; if($is_allow_edit_result) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaIsFinish" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			
			<div style='clear:both; padding-top: 15px;' class="data-table">
				<div class="caption">		
					<h2>Медицинское заключение</h2>
				</div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Серия: <?php if($is_allow_edit_result && $EvnPLDispDriver_IsFinish == 2) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputMedSer" style='color:#000;' class="link" dataid='{EvnPLDispDriver_MedSer}'><?php } echo empty($EvnPLDispDriver_MedSer)?$empty_str:'{EvnPLDispDriver_MedSer}'; if($is_allow_edit_result) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaMedSer" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Номер: <?php if($is_allow_edit_result && $EvnPLDispDriver_IsFinish == 2) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputMedNum" style='color:#000;' class="link" dataid='{EvnPLDispDriver_MedNum}'><?php } echo empty($EvnPLDispDriver_MedNum)?$empty_str:'{EvnPLDispDriver_MedNum}'; if($is_allow_edit_result) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaMedNum" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата: <?php if($is_allow_edit_result && $EvnPLDispDriver_IsFinish == 2) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputMedDate" style='color:#000;' class="link" dataid='{EvnPLDispDriver_MedDate}'><?php } echo empty($EvnPLDispDriver_MedDate)?$empty_str:'{EvnPLDispDriver_MedDate}'; if($is_allow_edit_result) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaMedDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Результат: <?php if($is_allow_edit_result && $EvnPLDispDriver_IsFinish == 2) { ?><span id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputResultDispDriver" style='color:#000;' class="link" dataid='{ResultDispDriver_id}'><?php } echo empty($ResultDispDriver_Name)?$empty_str:'{ResultDispDriver_Name}'; if($is_allow_edit_result) { ?></span><?php } ?></div><div id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_inputareaResultDispDriver" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div class="caption"><h2>Категории ТС, на управлении которыми предоставляется право</h2></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;' id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_DriverCategory"></div></div>
				<div class="caption"><h2>Медицинские ограничения к управлению ТС</h2></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;' id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_DriverMedicalClose"></div></div>
				<div class="caption"><h2>Медицинские показания к управлению ТС</h2></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;' id="EvnPLDispDriver_data_{EvnPLDispDriver_id}_DriverMedicalIndication"></div></div>
			</div>
			
		</div>
	</div>
</div>