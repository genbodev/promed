<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
$is_registry = ($Person_id == $MorbusProf_pid);
?>
<div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}" class="specifics">
    <div style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
        <!-- Диагноз (Регистр)-->
	    {EvnDiagProf}
    </div>
	<div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_toolbarMorbusProf" class="toolbar" style="display: none">
		<a id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_saveMorbusProf" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>
	<div class="data-row-container"><div class="data-row">Диагноз: <span class="value"><?php echo empty($Diag_Name) ? $empty_str : '{Diag_Name}'; ?></span></div></div>
	<div class="data-row-container"><div class="data-row">Заболевание: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputMorbusProfDiag" filterbydiagid="{Diag_id}" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusProfDiag_id) ? $empty_str : '{MorbusProfDiag_Name}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaMorbusProfDiag" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Опасный производственный фактор: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputHarmWorkFactorType" class="value"<?php } else { echo ' class="value"';} ?>><?php echo empty($HarmWorkFactorType_id) ? $empty_str : '{HarmWorkFactorType_Name}'; ?></span></div></div>
	<div class="data-row-container"><div class="data-row">Внешняя причина: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputDiag" class="value"<?php } else { echo ' class="value"';} ?>><?php echo empty($Diag_oid) ? $empty_str : '{Diag_oName}'; ?></span></div></div>

	<fieldset class="clear" style="padding: 5px;">
		<legend>Стаж работы в условиях вредного воздействия</legend>
		<div class="data-row-container"><div class="data-row">лет: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputMorbusProf_Year" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusProf_Year) ? $empty_str : '{MorbusProf_Year}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaMorbusProf_Year" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">месяцев: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputMorbusProf_Month" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusProf_Month) ? $empty_str : '{MorbusProf_Month}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaMorbusProf_Month" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">дней: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputMorbusProf_Day" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusProf_Day) ? $empty_str : '{MorbusProf_Day}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaMorbusProf_Day" class="input-area"></div></div>
	</fieldset>

	<div class="data-row-container"><div class="data-row">Профпригодность: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputMorbusProf_IsFit" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusProf_IsFit) ? $empty_str : '{MorbusProfIsFit_Name}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaMorbusProf_IsFit" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Организация: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputOrg" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Org_id) ? $empty_str : '{Org_Name}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaOrg" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Профессия: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputOnkoOccupationClass" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($OnkoOccupationClass_id) ? $empty_str : '{OnkoOccupationClass_Name}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaOnkoOccupationClass" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">МО, установившая диагноз: <span<?php if ($is_allow_edit) { ?> id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputLpu" class="value"<?php } else { echo ' class="value"';} ?>><?php echo empty($Lpu_iid) ? $empty_str : '{Lpu_Name}'; ?></span></div><div id="MorbusProf_{MorbusProf_pid}_{MorbusProf_id}_inputareaLpu" class="input-area"></div></div>

	<div class="clear"><br></div>
</div>