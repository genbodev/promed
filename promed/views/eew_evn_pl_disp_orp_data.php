<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>
<div class="left">
	<div id="EvnPLDispOrp_data_{EvnPLDispOrp_id}">
		<div class="caption">
			<h2>{DispClass_Name}</h2>
		</div>
		<div class="text">
			<p>Автор документа: {AuthorInfo}</p>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Вид оплаты: <?php if($is_allow_edit) { ?><span id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputPayType" style='color:#000;' class="link" dataid='{PayType_id}'><?php } echo empty($PayType_Name)?$empty_str:'{PayType_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputareaPayType" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата начала диспансеризации: <?php if($is_allow_edit) { ?><span id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputSetDate" style='color:#000;' class="link" dataid='{EvnPLDispOrp_setDate}'><?php } echo empty($EvnPLDispOrp_setDate)?$empty_str:'{EvnPLDispOrp_setDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputareaSetDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<!-- Информированное добровольное согласие -->
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Дата подписания согласия/отказа: <?php if($is_allow_edit) { ?><span id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputConsDate" style='color:#000;' class="link" dataid='{EvnPLDispOrp_consDate}'><?php } echo empty($EvnPLDispOrp_consDate)?$empty_str:'{EvnPLDispOrp_consDate}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputareaConsDate" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			{DopDispInfoConsent}
			<!-- Осмотр врача-специалиста -->
			{EvnVizitDispOrp}
			<!-- Обследования -->
			{EvnUslugaDispOrp}
			<!-- Диагнозы и рекомендации -->
			{EvnDiagAndRecomendation}
			<!-- Состояние здоровья до проведения диспансеризации / профосмотра -->
			{EvnDiagDopDispAndRecomendation}
			<!-- Общая оценка здоровья -->
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Группа здоровья: <?php if($is_allow_edit) { ?><span id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputHealthKind" style='color:#000;' class="link" dataid='{HealthKind_id}'><?php } echo empty($HealthKind_Name)?$empty_str:'{HealthKind_Name}'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputareaHealthKind" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
			<div style='clear:both;'><div style='float:left;padding:5px 0;'>Случай профосмотра закончен: <?php if($is_allow_edit) { ?><span id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputIsFinish" style='color:#000;' class="link" dataid='{EvnPLDispOrp_IsFinish}'><?php } echo ($EvnPLDispOrp_IsFinish == 2)?'Да':'Нет'; if($is_allow_edit) { ?></span><?php } ?></div><div id="EvnPLDispOrp_data_{EvnPLDispOrp_id}_inputareaIsFinish" class="input-area" style="float:left; margin-left:5px; display: none"></div></div>
		</div>
	</div>
</div>