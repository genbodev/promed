<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
?>
<div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}" class="specifics">
	<div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_toolbarMorbusVener" class="toolbar" style="display: none">
		<a id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_saveMorbusVener" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>


	<div class="data-row-container"><div class="data-row">Дата установления диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_DiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_DiagDT) ? $empty_str : '{MorbusVener_DiagDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_DiagDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Обстоятельства выявления заболевания: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputVenerDetectType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($VenerDetectType_id) ? $empty_str : '{VenerDetectType_Name}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaVenerDetectType" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Посещал пункт индивидуальной профилактики венерических болезней: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_IsVizitProf" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_IsVizitProf) ? $empty_str : '{IsVizitProf_Name}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_IsVizitProf" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Ознакомлен с предупреждением: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_IsPrevent" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_IsPrevent) ? $empty_str : '{IsPrevent_Name}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_IsPrevent" class="input-area"></div></div>

	<div class="data-row-container"><div class="data-row">Дата изменения диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_updDiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_updDiagDT) ? $empty_str : '{MorbusVener_updDiagDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_updDiagDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Дата госпитализации: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_HospDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_HospDT) ? $empty_str : '{MorbusVener_HospDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_HospDT" class="input-area"></div></div>

	{MorbusVenerContact}<!--Члены семьи и контакты, подлежащие обследованию-->
	{MorbusVenerTreatSyph}<!--Лечение больного сифилисом-->

	{MorbusVenerAccurTreat}<!--Контроль за аккуратностью лечения-->

	<div class="data-row-container"><div class="data-row">Дата начала лечения: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_BegTretDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_BegTretDT) ? $empty_str : '{MorbusVener_BegTretDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_BegTretDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">ЛПУ, где начал лечение: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputLpu_bid" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Lpu_bid) ? $empty_str : '{LpuBid_Nick}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaLpu_bid" class="input-area"></div></div>

	<div class="data-row-container"><div class="data-row">Дата окончания лечения: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_EndTretDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_EndTretDT) ? $empty_str : '{MorbusVener_EndTretDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_EndTretDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">ЛПУ, где окончил лечение: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputLpu_eid" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Lpu_eid) ? $empty_str : '{LpuEid_Nick}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaLpu_eid" class="input-area"></div></div>

	{MorbusVenerEndTreat}<!--Контроль по окончании лечения-->
	
	<div class="data-row-container"><div class="data-row">Жилищно-бытовые условия: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_LiveCondit" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_LiveCondit) ? $empty_str : '{MorbusVener_LiveCondit}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_LiveCondit" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Условия работы: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_WorkCondit" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_WorkCondit) ? $empty_str : '{MorbusVener_WorkCondit}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_WorkCondit" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Наследственность: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_Heredity" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_Heredity) ? $empty_str : '{MorbusVener_Heredity}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_Heredity" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Употребление алкоголя, наркотиков: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_UseAlcoNarc" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_UseAlcoNarc) ? $empty_str : '{MorbusVener_UseAlcoNarc}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_UseAlcoNarc" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Где произошло заражение: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_PlaceInfect" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_PlaceInfect) ? $empty_str : '{MorbusVener_PlaceInfect}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_PlaceInfect" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Заражение произошло в состоянии опьянения: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_IsAlco" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_IsAlco) ? $empty_str : '{IsAlco_Name}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_IsAlco" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Менструация с (лет): <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_MensBeg" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_MensBeg) ? $empty_str : '{MorbusVener_MensBeg}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_MensBeg" class="input-area"></div></div>
		
	<div class="data-row-container"><div class="data-row">Менструация по (лет): <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_MensEnd" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_MensEnd) ? $empty_str : '{MorbusVener_MensEnd}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_MensEnd" class="input-area"></div></div>

	<div class="data-row-container"><div class="data-row">Менструация через (дней): <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_MensOver" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_MensOver) ? $empty_str : '{MorbusVener_MensOver}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_MensOver" class="input-area"></div></div>	
		
	<div class="data-row-container"><div class="data-row">Дата последней менструации: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_MensLastDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_MensLastDT) ? $empty_str : '{MorbusVener_MensLastDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_MensLastDT" class="input-area"></div></div>

	<div class="data-row-container"><div class="data-row">Половая жизнь с (лет): <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_SexualInit" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_SexualInit) ? $empty_str : '{MorbusVener_SexualInit}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_SexualInit" class="input-area"></div></div>
		
	<div class="data-row-container"><div class="data-row">Количество беременностей: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_CountPregnancy" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_CountPregnancy) ? $empty_str : '{MorbusVener_CountPregnancy}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_CountPregnancy" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Закончились родами: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_CountBirth" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_CountBirth) ? $empty_str : '{MorbusVener_CountBirth}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_CountBirth" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Прерваны абортом: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_CountAbort" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_CountAbort) ? $empty_str : '{MorbusVener_CountAbort}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_CountAbort" class="input-area"></div></div>
		
	<div class="data-row-container"></div>

	<div class="data-row-container"><div class="data-row">Дата снятия с учета: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputMorbusVener_DeRegDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusVener_DeRegDT) ? $empty_str : '{MorbusVener_DeRegDT}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaMorbusVener_DeRegDT" class="input-area"></div></div>

	<div class="data-row-container"><div class="data-row">Причина снятия с учета: <span<?php if ($is_allow_edit) { ?> id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputVenerDeRegCauseType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($VenerDeRegCauseType_id) ? $empty_str : '{VenerDeRegCauseType_Name}'; ?></span></div><div id="MorbusVener_{MorbusVener_pid}_{MorbusVener_id}_inputareaVenerDeRegCauseType" class="input-area"></div></div>



	<div class="clear"><br></div>
</div>



