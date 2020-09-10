<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>
<div class="left">
	<div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}">
		<div class="caption">
			<h2>
				Случай медицинского освидетельствования мигранта №{EvnPLDispMigrant_Num} от {EvnPLDispMigrant_setDate}<br>
				{Lpu_Nick}
			</h2>
		</div>
		<div class="text">
			<!-- Информированное добровольное согласие -->
			<h3>Информированное добровольное согласие</h3>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид оплаты: <?php if($is_allow_edit) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputPayType" style='color:#000;' class="link" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата подписания согласия/отказа: <?php if($is_allow_edit) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputConsDate" style='color:#000;' class="link" dataid='{EvnPLDispMigrant_consDate}'><?php } echo empty($EvnPLDispMigrant_consDate)?$empty_str:'{EvnPLDispMigrant_consDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaConsDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Планируемый период пребывания в РФ: <?php if($is_allow_edit) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputRFDateRange" style='color:#000;' class="link" dataid='{EvnPLDispMigrant_RFDateRange}'><?php } echo empty($EvnPLDispMigrant_RFDateRange)?$empty_str:'{EvnPLDispMigrant_RFDateRange}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaRFDateRange" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			{DopDispInfoConsent}
			<!-- Маршрутная карта -->
			{EvnUslugaDispDop}
			<!-- Файлы -->
			{EvnMediaData}
			<!-- Результат -->
			<h3>Результат</h3>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Медицинское обследование закончено: <?php if($is_allow_edit) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputIsFinish" style='color:#000;' class="link" dataid='{EvnPLDispMigrant_IsFinish}'><?php } echo empty($EvnPLDispMigrant_IsFinish_Name)?$empty_str:'{EvnPLDispMigrant_IsFinish_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaIsFinish" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Результат: <?php if($is_allow_edit) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputResultDispMigrant" style='color:#000;' class="link" dataid='{ResultDispMigrant_id}'><?php } echo empty($ResultDispMigrant_Name)?$empty_str:'{ResultDispMigrant_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaResultDispMigrant" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			
			<div style='clear:both; padding-top: 15px;' class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertHIV_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertHIV_toolbar').style.display='none'">
				<div class="caption">		
					<h2>Сертификат об обследовании на ВИЧ</h2>
					<div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertHIV_toolbar" class="toolbar">
						<a id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertHIV_print" class="button icon icon-print16" title="Печать"><span></span></a>
					</div>
				</div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Номер: <?php if($is_allow_edit && empty($IsHiv)) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputSertHIVNumber" style='color:#000;' class="link" dataid='{EvnPLDispMigran_SertHIVNumber}'><?php } echo empty($EvnPLDispMigran_SertHIVNumber)?$empty_str:'{EvnPLDispMigran_SertHIVNumber}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaSertHIVNumber" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата: <?php if($is_allow_edit && empty($IsHiv)) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputSertHIVDate" style='color:#000;' class="link" dataid='{EvnPLDispMigran_SertHIVDate}'><?php } echo empty($EvnPLDispMigran_SertHIVDate)?$empty_str:'{EvnPLDispMigran_SertHIVDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaSertHIVDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			</div>
			
			
			<div style='clear:both; padding-top: 15px;' class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertInfect_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertInfect_toolbar').style.display='none'">
				<div class="caption">		
					<h2>Мед. заключение об инфекционных заболеваниях</h2>
					<div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertInfect_toolbar" class="toolbar">
						<a <?php if (empty($IsInfect)) { ?> id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertInfect_print" <?php } ?> class="button icon icon-print16" title="Печать"><span></span></a>
					</div>
				</div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Номер: <?php if($is_allow_edit && empty($IsInfect)) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputSertInfectNumber" style='color:#000;' class="link" dataid='{EvnPLDispMigran_SertInfectNumber}'><?php } echo empty($EvnPLDispMigran_SertInfectNumber)?$empty_str:'{EvnPLDispMigran_SertInfectNumber}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaSertInfectNumber" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата: <?php if($is_allow_edit && empty($IsInfect)) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputSertInfectDate" style='color:#000;' class="link" dataid='{EvnPLDispMigran_SertInfectDate}'><?php } echo empty($EvnPLDispMigran_SertInfectDate)?$empty_str:'{EvnPLDispMigran_SertInfectDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaSertInfectDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			</div>
			
			<div style='clear:both; padding-top: 15px;' class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertNarco_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertNarco_toolbar').style.display='none'">
				<div class="caption">		
					<h2>Мед. заключение о наркомании</h2>
					<div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertNarco_toolbar" class="toolbar">
						<a id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_SertNarco_print" class="button icon icon-print16" title="Печать"><span></span></a>
					</div>
				</div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Номер: <?php if($is_allow_edit && empty($IsNarco)) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputSertNarcoNumber" style='color:#000;' class="link" dataid='{EvnPLDispMigran_SertNarcoNumber}'><?php } echo empty($EvnPLDispMigran_SertNarcoNumber)?$empty_str:'{EvnPLDispMigran_SertNarcoNumber}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaSertNarcoNumber" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
				<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата: <?php if($is_allow_edit && empty($IsNarco)) { ?><span id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputSertNarcoDate" style='color:#000;' class="link" dataid='{EvnPLDispMigran_SertNarcoDate}'><?php } echo empty($EvnPLDispMigran_SertNarcoDate)?$empty_str:'{EvnPLDispMigran_SertNarcoDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispMigrant_data_{EvnPLDispMigrant_id}_inputareaSertNarcoDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			</div>
			<!-- Контактные лица -->
			<?php if ($IsInfected > 0) { ?>
			{MigrantContact}
			<?php } ?>
		</div>
	</div>
</div>