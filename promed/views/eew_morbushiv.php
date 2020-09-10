<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = ('edit' == $accessType);//'edit'
?>
<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}" class="specifics"<?php
if ( $is_allow_edit ) {
?> onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toolbar').style.display='none'"<?php
} 
?>>
	<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toolbar" class="toolbar" style="display: none">
		<a id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_save" class="button icon icon-save16" title="Сохранить" style="display: none"><span></span></a>
	</div>
	<div class="data-row-container">
		<div class="data-row">Гражданство: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputHIVContingentTypeP" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{HIVContingentTypeP_id}'><?php echo empty($HIVContingentTypeP_id_Name) ? $empty_str : '{HIVContingentTypeP_id_Name}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaHIVContingentTypeP" class="input-area"></div>
	</div>
	<fieldset class="data-row-container" style="padding-left: 15px">
		<legend>Код контингента</legend>
	<div class="data-row-container">
		<div class="data-row"><span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputHIVContingentTypeIdList" class="value link"<?php } else { echo ' class="value"';} ?> dataidlist='{HIVContingentType_id_list}'><?php echo empty($HIVContingentType_Name_list) ? $empty_str : '{HIVContingentType_Name_list}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaHIVContingentTypeIdList" class="input-area"></div>
	</div>
	</fieldset>
	<?php if ( 'kz' != getRegionNick() ) { ?>
	<div class="data-row-container">
		<div class="data-row">Дата подтверждения диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputConfirmDate" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusHIV_confirmDate}'><?php echo empty($MorbusHIV_confirmDate) ? $empty_str : '{MorbusHIV_confirmDate}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaConfirmDate" class="input-area"></div>
	</div>
	<div class="data-row-container">
		<div class="data-row">Эпидемиологический код: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputEpidemCode" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusHIV_EpidemCode}'><?php echo empty($MorbusHIV_EpidemCode) ? $empty_str : '{MorbusHIV_EpidemCode}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaEpidemCode" class="input-area"></div>
	</div>
	<?php } ?>
	<div class="data-row-container">
		<div class="data-row">Предполагаемый путь инфицирования: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputHIVPregPathTransType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{HIVPregPathTransType_id}'><?php echo empty($HIVPregPathTransType_id_Name) ? $empty_str : '{HIVPregPathTransType_id_Name}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaHIVPregPathTransType" class="input-area"></div>
	</div>
	<div class="data-row-container">
		<div class="data-row">Дата установления диагноза ВИЧ-инфекции: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputDiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIV_DiagDT) ? $empty_str : '{MorbusHIV_DiagDT}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaDiagDT" class="input-area"></div>
	</div>
	<div class="data-row-container">
		<div class="data-row">Тип вируса: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputHIVInfectType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{HIVInfectType_id}'><?php echo empty($HIVInfectType_id_Name) ? $empty_str : '{HIVInfectType_id_Name}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaHIVInfectType" class="input-area"></div>
	</div>
	<div class="data-row-container">
		<div class="data-row">Стадия ВИЧ-инфекции: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputHIVPregInfectStudyType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{HIVPregInfectStudyType_id}'><?php echo empty($HIVPregInfectStudyType_id_Name) ? $empty_str : '{HIVPregInfectStudyType_id_Name}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaHIVPregInfectStudyType" class="input-area"></div>
	</div>
	<fieldset class="data-row-container" style="padding-left: 15px">
		<legend>Иммунный статус: CD4 Т-лимфоциты</legend>
		<div class="data-row-container">
			<div class="data-row">Количество (мм): <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputCountCD4" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIV_CountCD4) ? $empty_str : '{MorbusHIV_CountCD4}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaCountCD4" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">% содержания: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputPartCD4" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIV_PartCD4) ? $empty_str : '{MorbusHIV_PartCD4}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaPartCD4" class="input-area"></div>
		</div>
	</fieldset>
	<div class="data-row-container">
		<div class="data-row">Дата снятия с диспансерного наблюдения: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputOutendDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVOut_endDT) ? $empty_str : '{MorbusHIVOut_endDT}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaOutendDT" class="input-area"></div>
	</div>
	<div class="data-row-container">
		<div class="data-row">Причина снятия с диспансерного наблюдения: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputHIVDispOutCauseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{HIVDispOutCauseType_id}'><?php echo empty($HIVDispOutCauseType_id_Name) ? $empty_str : '{HIVDispOutCauseType_id_Name}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaHIVDispOutCauseType" class="input-area"></div>
	</div>
	<div class="data-row-container">
		<div class="data-row">Причина смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputDiagD" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{DiagD_id}'><?php echo empty($DiagD_id_Name) ? $empty_str : '{DiagD_id_Name}'; ?></span></div>
		<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaDiagD" class="input-area"></div>
	</div>
<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}" class="data-table"<?php
if ( $is_allow_edit ) {
?> onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toolbarLab').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toolbarLab').style.display='none'"<?php
} 
?>>
    <div class="caption">
        <h2><span id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toggleDisplayMorbusHIVLab" class="<?php echo isset($MorbusHIVLab_id)?'collapsible':'collapsible-empty'; ?>">Лабораторная диагностика ВИЧ-инфекции</span></h2>
        <div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_toolbarLab" class="toolbar">
            <a id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_saveLab" class="button icon icon-save16" title="Сохранить" style="display: none;"><span></span></a>
        </div>
    </div>

    <div id="MorbusHIVLabData_{MorbusHIV_pid}_{Morbus_id}" style="display: <?php echo (empty($MorbusHIVLab_id))?'none':'block'; ?>;">		
	<fieldset class="data-row-container" style="padding-left: 15px">
		<legend>Результат реакции иммуноблота</legend>
		<div class="data-row-container">
			<div class="data-row">Дата постановки: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputBlotDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_BlotDT) ? $empty_str : '{MorbusHIVLab_BlotDT}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaBlotDT" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Тип тест-системы: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputTestSystem" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_TestSystem) ? $empty_str : '{MorbusHIVLab_TestSystem}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaTestSystem" class="input-area"></div>
		</div>
        <div class="data-row-container">
            <div class="data-row">№ серии: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputBlotNum" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_BlotNum) ? $empty_str : '{MorbusHIVLab_BlotNum}'; ?></span></div>
            <div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaBlotNum" class="input-area"></div>
        </div>
        <div class="data-row-container">
            <div class="data-row">№ иммуноблота: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputNumImmun" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIV_NumImmun) ? $empty_str : '{MorbusHIV_NumImmun}'; ?></span></div>
            <div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaNumImmun" class="input-area"></div>
        </div>
		<div class="data-row-container">
			<div class="data-row">Выявленные белки и гликопротеиды: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputBlotResult" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_BlotResult) ? $empty_str : '{MorbusHIVLab_BlotResult}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaBlotResult" class="input-area"></div>
		</div>
		<?php if ( 'kz' != getRegionNick() ) { ?>
		<div class="data-row-container">
			<div class="data-row">Результат: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputLabAssessmentResultI" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{LabAssessmentResult_iid}'><?php echo empty($LabAssessmentResult_iid) ? $empty_str : '{LabAssessmentResult_iName}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaLabAssessmentResultI" class="input-area"></div>
		</div>
		<?php } ?>
	</fieldset>
	<fieldset class="data-row-container" style="padding-left: 15px">
		<legend>ИФА</legend>
		<div class="data-row-container">
			<div class="data-row">Учреждение, первично выявившее положительный результат в ИФА: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputLpuifa" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Lpuifa_id}'><?php echo empty($Lpuifa_id_Name) ? $empty_str : '{Lpuifa_id_Name}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaLpuifa" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Дата ИФА: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputIFADT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_IFADT) ? $empty_str : '{MorbusHIVLab_IFADT}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaIFADT" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Результат ИФА: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputIFAResult" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_IFAResult) ? $empty_str : '{MorbusHIVLab_IFAResult}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaIFAResult" class="input-area"></div>
		</div>
	</fieldset>
	<fieldset class="data-row-container" style="padding-left: 15px">
		<legend>Полимеразная цепная реакция</legend>
		<div class="data-row-container">
			<div class="data-row">Дата: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputPCRDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_PCRDT) ? $empty_str : '{MorbusHIVLab_PCRDT}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaPCRDT" class="input-area"></div>
		</div>
		<?php if ( 'kz' != getRegionNick() ) { ?>
		<div class="data-row-container">
			<div class="data-row">Результат: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputLabAssessmentResultC" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{LabAssessmentResult_cid}'><?php echo empty($LabAssessmentResult_cid) ? $empty_str : '{LabAssessmentResult_cName}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaLabAssessmentResultC" class="input-area"></div>
		</div>
		<?php } ?>
		<div class="data-row-container">
			<div class="data-row">Результат: <span<?php if ($is_allow_edit) { ?> id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputPCRResult" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusHIVLab_PCRResult) ? $empty_str : '{MorbusHIVLab_PCRResult}'; ?></span></div>
			<div id="MorbusHIV_{MorbusHIV_pid}_{Morbus_id}_inputareaPCRResult" class="input-area"></div>
		</div>
	</fieldset>
	</div>
</div>

	{MorbusHIVChem}<!--Раздел «Проведение химиопрофилактики ВИЧ-инфекции»-->
	{MorbusHIVVac}<!--Раздел «Вакцинация»-->
	{MorbusHIVSecDiag}<!--Раздел «Вторичные заболевания и оппортунистические инфекции»-->
</div>



