<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
$is_registry = ($Person_id == $MorbusIBS_pid);
?>
<div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}" class="specifics">
	<div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_toolbarMorbusIBS" class="toolbar" style="display: none">
		<a id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_saveMorbusIBS" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>
	<div class="data-row-container"><div class="data-row">Тип ИБС: <span class="value" id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_outputIBSType">{IBSType_Name}</span></div></div>
	<div class="data-row-container"><div class="data-row">Диагноз ИБС: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputDiag" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Diag_id) ? $empty_str : '{Diag_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaDiag" class="input-area"></div></div>
	<fieldset class="clear" style="padding: 5px;">
	<legend>Клиническая картина</legend>
		<fieldset class="clear" style="padding: 5px;">
		<legend>Стабильная стенокардия</legend>
			<div class="data-row-container"><div class="data-row">Стабильная стенокардия: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsStenocardia" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsStenocardia) ? $empty_str : '{IsStenocardia_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsStenocardia" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Функциональный класс: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_FuncClass" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_FuncClass) ? $empty_str : '{MorbusIBS_FuncClass}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_FuncClass" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Стресс-тест: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputIBSStressTest" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($IBSStressTest_id) ? $empty_str : '{IBSStressTest_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaIBSStressTest" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Эхокардиография: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsEchocardiography" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsEchocardiography) ? $empty_str : '{IsEchocardiography_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsEchocardiography" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Холтеровское мониторирование: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsHalterMonitor" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsHalterMonitor) ? $empty_str : '{IsHalterMonitor_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsHalterMonitor" class="input-area"></div></div>
		</fieldset>
		<div class="data-row-container"><div class="data-row">Перенесенный инфаркт миокарда: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsMyocardInfarct" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsMyocardInfarct) ? $empty_str : '{IsMyocardInfarct_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsMyocardInfarct" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Выраженная ишемия миокарда при нагрузке: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsMyocardIschemia" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsMyocardIschemia) ? $empty_str : '{IsMyocardIschemia_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsMyocardIschemia" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Впервые возникшая стенокардия: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsFirstStenocardia" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsFirstStenocardia) ? $empty_str : '{IsFirstStenocardia_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsFirstStenocardia" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Нестабильная (прогрессирующая) стенокардия: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsNoStableStenocardia" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsNoStableStenocardia) ? $empty_str : '{IsNoStableStenocardia_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsNoStableStenocardia" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Подъем ST: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsRiseTS" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsRiseTS) ? $empty_str : '{IsRiseTS_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsRiseTS" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Сохраняются и/или рецидивируют признаки ишемии: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsSaveIschemia" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsSaveIschemia) ? $empty_str : '{IsSaveIschemia_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsSaveIschemia" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Возврат стенокардии: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsBackStenocardia" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsBackStenocardia) ? $empty_str : '{IsBackStenocardia_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsBackStenocardia" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Перенесенная операция коронарного шунтирования: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsShunting" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsShunting) ? $empty_str : '{IsShunting_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsShunting" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Перенесенная операция коронарного стентирования: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsStenting" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsStenting) ? $empty_str : '{IsStenting_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsStenting" class="input-area"></div></div>
	</fieldset>
	<fieldset class="clear" style="padding: 5px;">
	<legend>Коронарография</legend>
		<div class="data-row-container"><div class="data-row">Показано проведение КГ: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsKGIndication" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsKGIndication) ? $empty_str : '{IsKGIndication_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsKGIndication" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Код: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputUslugaComplex" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($UslugaComplex_id) ? $empty_str : '{UslugaComplex_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaUslugaComplex" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Получено согласие на проведение КГ: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsKGConsent" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsKGConsent) ? $empty_str : '{IsKGConsent_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsKGConsent" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Проведена КГ: <span<?php if ($is_allow_edit) { ?> id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputMorbusIBS_IsKGFinished" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusIBS_IsKGFinished) ? $empty_str : '{IsKGFinished_Name}'; ?></span></div><div id="MorbusIBS_{MorbusIBS_pid}_{MorbusIBS_id}_inputareaMorbusIBS_IsKGFinished" class="input-area"></div></div>
	</fieldset>
	<div class="clear"><br></div>
</div>