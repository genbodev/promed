<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = ('edit' == $accessType);
?>
<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}" class="specifics" sopdiagid="{EvnDiagPLSop_id}">
	<div class="caption">
		<h2><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayDiag" class="collapsible">Диагноз</span></h2>
		<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toolbarDiag" class="toolbar" style="display: none">
			<a id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_saveDiag" class="button icon icon-save16" title="Сохранить"><span></span></a>
		</div>
	</div>
	<div id="MorbusOnkoDiag_{MorbusOnko_pid}_{Morbus_id}" style="display: <?php echo (empty($MorbusOnko_NumTumor))?'none':'block'; ?>; padding:0px 5px 25px; border: 1px solid #000;">
        <?php if(getRegionNick() != 'kz') { ?>
		<div class="data-row-container"><div class="data-row">Повод обращения*: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoTreatment" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoTreatment_id}'><?php echo empty($OnkoTreatment_id_Name) ? $empty_str : '{OnkoTreatment_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoTreatment" class="input-area"></div></div>
		<?php } ?>
		<div class="data-row-container"><div class="data-row">Дата появления первых признаков заболевания: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputfirstSignDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_firstSignDT) ? $empty_str : '{MorbusOnko_firstSignDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareafirstSignDT" class="input-area"></div></div>
		<div class="data-row-container">
			<div class="data-row">Первое обращение в МО по поводу данного заболевания. Дата: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputfirstVizitDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_firstVizitDT) ? $empty_str : '{MorbusOnko_firstVizitDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareafirstVizitDT" class="input-area"></div>
			<div class="data-row seq">МО: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputLpu" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Lpu_foid}'><?php echo empty($Lpu_foid_Name) ? $empty_str : '{Lpu_foid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaLpu" class="input-area"></div>
		</div>
		<div class="data-row-container"><div class="data-row">Дата установления диагноза<?php if(getRegionNick() == 'perm'){?>*<?php } ?>: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputsetDiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_setDiagDT) ? $empty_str : '{MorbusOnko_setDiagDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareasetDiagDT" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Регистрационный номер: <span<?php if ($is_allow_edit && havingGroup('OnkoRegistryFullAccess')) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputNumCard" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnkoBase_NumCard) ? $empty_str : '{MorbusOnkoBase_NumCard}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaNumCard" class="input-area"></div></div>
		<div class="data-row-container">
			<div class="data-row">Дата взятия на учет в ОД: <span<?php if ($is_allow_edit && havingGroup('OnkoRegistryFullAccess')) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputMorbusBaseSetDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusBase_setDT) ? $empty_str : '{MorbusBase_setDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaMorbusBaseSetDT" class="input-area"></div>
			<div class="data-row seq">Взят на учет в ОД: <span<?php if ($is_allow_edit && havingGroup('OnkoRegistryFullAccess')) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoRegType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoRegType_id}'><?php echo empty($OnkoRegType_id_Name) ? $empty_str : '{OnkoRegType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoRegType" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Дата снятия с учета в ОД: <span<?php if ($is_allow_edit && havingGroup('OnkoRegistryFullAccess')) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputMorbusBaseDisDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusBase_disDT) ? $empty_str : '{MorbusBase_disDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaMorbusBaseDisDT" class="input-area"></div>
			<div class="data-row seq">Причина снятия с учета: <span<?php if ($is_allow_edit && havingGroup('OnkoRegistryFullAccess')) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoRegOutType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoRegOutType_id}'><?php echo empty($OnkoRegOutType_id_Name) ? $empty_str : '{OnkoRegOutType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoRegOutType" class="input-area"></div>
		</div>
		<div class="data-row-container"><div class="data-row">Порядковый номер данной опухоли у данного больного: <span class="value"><?php echo empty($MorbusOnko_NumTumor) ? $empty_str : '{MorbusOnko_NumTumor}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Число первичных злокачественных новообразований: <span class="value"><?php echo empty($MorbusOnko_CountFirstTumor) ? $empty_str : '{MorbusOnko_CountFirstTumor}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Первично-множественная опухоль: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorPrimaryMultipleType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorPrimaryMultipleType_id}'><?php echo empty($TumorPrimaryMultipleType_id_Name) ? $empty_str : '{TumorPrimaryMultipleType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorPrimaryMultipleType" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Признак основной опухоли: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsMainTumor" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsMainTumor}'><?php echo empty($MorbusOnko_IsMainTumor_Name) ? $empty_str : '{MorbusOnko_IsMainTumor_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsMainTumor" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Топография (локализация) опухоли: <span class="value" id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_DiagValue" dataid="<?php echo empty($Diag_id) ? '' : '{Diag_id}'; ?>"><?php echo empty($Diag_id_Name) ? $empty_str : '{Diag_id_Name}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Сторона поражения: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoLesionSide" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoLesionSide_id}'><?php echo empty($OnkoLesionSide_id_Name) ? $empty_str : '{OnkoLesionSide_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoLesionSide" class="input-area"></div></div>

		<h2 style='clear:both;'><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayHisto" class="collapsible">Подтверждение диагноза</span></h2>
		<div id="MorbusOnkoHisto_{MorbusOnko_pid}_{Morbus_id}" style="display: <?php echo (empty($HistologicReasonType_id) && empty($MorbusOnko_histDT))?'none':'block'; ?>; padding:0px 5px 25px; border: 1px solid #000;">
			<div class="data-row-container"><div class="data-row">Отказ / противопоказание: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputHistologicReasonType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{HistologicReasonType_id}'><?php echo empty($HistologicReasonType_id_Name) ? $empty_str : '{HistologicReasonType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaHistologicReasonType" class="input-area"></div></div>
			<div class="data-row-container" id="MorbusOnko_{MorbusOnko_pid}_contMorbusOnkoHistDT" style="display: <?php echo (empty($HistologicReasonType_id)?'none':'block'); ?>"><div class="data-row">Дата регистрации отказа / противопоказания<span id="MorbusOnko_{MorbusOnko_pid}_histDT_allowBlank" style="display: <?php echo ($histDT_AllowBlank == false ? 'inline' : 'none');  ?>">*</span>: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputMorbusOnkoHistDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_histDT) ? $empty_str : '{MorbusOnko_histDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaMorbusOnkoHistDT" class="input-area"></div></div>

			{MorbusOnkoLink}

		</div>

		
		
		
		
        
		<h2 style='clear:both;'><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayMorfo" class="collapsible">Морфологический тип опухоли</span></h2>
		<div id="MorbusOnkoMorfo_{MorbusOnko_pid}_{Morbus_id}" style="display: <?php echo (empty($OnkoDiag_mid) && empty($MorbusOnko_NumHisto))?'none':'block'; ?>; padding:0px 5px 25px; border: 1px solid #000;">
			<div class="data-row-container"><div class="data-row">Морфологический тип опухоли. (Гистология опухоли): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoDiag" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoDiag_mid}'><?php echo empty($OnkoDiag_mid_Name) ? $empty_str : '{OnkoDiag_mid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoDiag" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Номер гистологического исследования: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputNumHisto" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_NumHisto) ? $empty_str : '{MorbusOnko_NumHisto}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaNumHisto" class="input-area"></div></div>
		</div>
		<h2 style='clear:both; margin-bottom: 0;'>Стадия опухолевого процесса по системе TNM</h2>
		<div style="padding:0px 5px 25px; margin: 0 0 15px;">
			<div class="data-row-container">
				<div class="data-row"><span style="display: inline-block; width: 120px; text-align: right;">ФОМС. T</span><span id="MorbusOnko_OnkoT_allowBlank" style="display: <?php echo ($OnkoT_AllowBlank == false ? 'inline' : 'none');  ?>">*</span>: <span<?php if ($is_allow_edit && $OnkoT_Enabled == true) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoTF" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoT_fid}'><?php echo !isset($OnkoT_fid_Name) ? $empty_str : '<span style="color: #c00;">{OnkoT_CodeStage}.</span> {OnkoT_fid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoTF" class="input-area"></div>
				<div class="data-row seq">N<span id="MorbusOnko_OnkoN_allowBlank" style="display: <?php echo ($OnkoN_AllowBlank == false ? 'inline' : 'none');  ?>">*</span>: <span<?php if ($is_allow_edit && $OnkoN_Enabled == true) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoNF" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoN_fid}'><?php echo !isset($OnkoN_fid_Name) ? $empty_str : '<span style="color: #c00;">{OnkoN_CodeStage}.</span> {OnkoN_fid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoNF" class="input-area"></div>
				<div class="data-row seq">M<span id="MorbusOnko_OnkoM_allowBlank" style="display: <?php echo ($OnkoM_AllowBlank == false ? 'inline' : 'none');  ?>">*</span>: <span<?php if ($is_allow_edit && $OnkoM_Enabled == true) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoMF" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoM_fid}'><?php echo !isset($OnkoM_fid_Name) ? $empty_str : '<span style="color: #c00;">{OnkoM_CodeStage}.</span> {OnkoM_fid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoMF" class="input-area"></div>
			</div>
			<div class="data-row-container">
				<div class="data-row"><span style="display: inline-block; width: 120px; text-align: right;">Канцер регистр. T</span><span style="display: inline">*</span>: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoT" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoT_id}'><?php echo !isset($OnkoT_id_Name) ? $empty_str : '{OnkoT_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoT" class="input-area"></div>
				<div class="data-row seq">N<span style="display: inline">*</span>: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoN" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoN_id}'><?php echo !isset($OnkoN_id_Name) ? $empty_str : '{OnkoN_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoN" class="input-area"></div>
				<div class="data-row seq">M<span style="display: inline">*</span>: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoM" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoM_id}'><?php echo !isset($OnkoM_id_Name) ? $empty_str : '{OnkoM_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoM" class="input-area"></div>
			</div>
		</div>
		<h2 style='clear:both; margin-bottom: 0;'>Стадия опухолевого процесса</h2>
		<div style="padding:0px 5px 25px; margin: 0 0 15px;">
			<div class="data-row-container"><div class="data-row"><span style="display: inline-block; width: 100px; text-align: right;">ФОМС</span><span id="MorbusOnko_TumorStage_allowBlank" style="display: <?php echo ($TumorStage_AllowBlank == false ? 'inline' : 'none');  ?>">*</span>: <span<?php if ($is_allow_edit && $TumorStage_Enabled == true) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorStageF" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorStage_fid}'><?php echo !isset($TumorStage_fid_Name) ? $empty_str : '<span style="color: #c00;">{TumorStage_CodeStage}.</span> {TumorStage_fid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorStageF" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row"><span style="display: inline-block; width: 100px; text-align: right;">Канцер регистр</span><span style="display: inline">*</span>: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorStage" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorStage_id}'><?php echo empty($TumorStage_id_Name) ? $empty_str : '{TumorStage_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorStage" class="input-area"></div></div>
		</div>
		<h2 style='clear:both;'><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayMeta" class="collapsible">Локализация отдаленных метастазов</span></h2>
		<div id="MorbusOnkoMeta_{MorbusOnko_pid}_{Morbus_id}" style="display: <?php echo (!empty($MorbusOnko_IsTumorDepoUnknown_Name) || !empty($MorbusOnko_IsTumorDepoLympha_Name) || !empty($MorbusOnko_IsTumorDepoBones_Name) || !empty($MorbusOnko_IsTumorDepoLiver_Name) || !empty($MorbusOnko_IsTumorDepoLungs_Name) || !empty($MorbusOnko_IsTumorDepoBrain_Name) || !empty($MorbusOnko_IsTumorDepoSkin_Name) || !empty($MorbusOnko_IsTumorDepoKidney_Name) || !empty($MorbusOnko_IsTumorDepoOvary_Name) || !empty($MorbusOnko_IsTumorDepoPerito_Name) || !empty($MorbusOnko_IsTumorDepoMarrow_Name) || !empty($MorbusOnko_IsTumorDepoOther_Name) || !empty($MorbusOnko_IsTumorDepoMulti_Name))?'block':'none'; ?>; padding:0px 5px 25px; border: 1px solid #000;">
			<div class="data-row-container"><div class="data-row">Неизвестна: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoUnknown" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoUnknown}'><?php echo empty($MorbusOnko_IsTumorDepoUnknown_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoUnknown_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoUnknown" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Отдаленные лимфатические узлы: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoLympha" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoLympha}'><?php echo empty($MorbusOnko_IsTumorDepoLympha_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoLympha_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoLympha" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Кости: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoBones" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoBones}'><?php echo empty($MorbusOnko_IsTumorDepoBones_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoBones_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoBones" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Печень: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoLiver" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoLiver}'><?php echo empty($MorbusOnko_IsTumorDepoLiver_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoLiver_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoLiver" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Легкие и/или плевра: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoLungs" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoLungs}'><?php echo empty($MorbusOnko_IsTumorDepoLungs_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoLungs_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoLungs" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Головной мозг: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoBrain" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoBrain}'><?php echo empty($MorbusOnko_IsTumorDepoBrain_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoBrain_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoBrain" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Кожа: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoSkin" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoSkin}'><?php echo empty($MorbusOnko_IsTumorDepoSkin_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoSkin_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoSkin" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Почки: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoKidney" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoKidney}'><?php echo empty($MorbusOnko_IsTumorDepoKidney_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoKidney_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoKidney" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Яичники: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoOvary" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoOvary}'><?php echo empty($MorbusOnko_IsTumorDepoOvary_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoOvary_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoOvary" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Брюшина: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoPerito" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoPerito}'><?php echo empty($MorbusOnko_IsTumorDepoPerito_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoPerito_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoPerito" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Костный мозг: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoMarrow" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoMarrow}'><?php echo empty($MorbusOnko_IsTumorDepoMarrow_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoMarrow_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoMarrow" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Другие органы: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoOther" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoOther}'><?php echo empty($MorbusOnko_IsTumorDepoOther_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoOther_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoOther" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Множественные: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputIsTumorDepoMulti" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusOnko_IsTumorDepoMulti}'><?php echo empty($MorbusOnko_IsTumorDepoMulti_Name) ? $empty_str : '{MorbusOnko_IsTumorDepoMulti_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaIsTumorDepoMulti" class="input-area"></div></div>
		</div>

        <div class="data-row-container"><div class="data-row">Выявлен врачом: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoPostType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoPostType_id}'><?php echo empty($OnkoPostType_id_Name) ? $empty_str : '{OnkoPostType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoPostType" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Обстоятельства выявления опухоли: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorCircumIdentType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorCircumIdentType_id}'><?php echo empty($TumorCircumIdentType_id_Name) ? $empty_str : '{TumorCircumIdentType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorCircumIdentType" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Причины поздней диагностики: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoLateDiagCause" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoLateDiagCause_id}'><?php echo empty($OnkoLateDiagCause_id_Name) ? $empty_str : '{OnkoLateDiagCause_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoLateDiagCause" class="input-area"></div></div>
        <div class="data-row-container">
            <div class="data-row">Дата смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputMorbusOnkoBaseDeadDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnkoBase_deadDT) ? $empty_str : '{MorbusOnkoBase_deadDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaMorbusOnkoBaseDeadDT" class="input-area"></div>
            <div class="data-row seq">Причина смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputDiagDead" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Diag_did}'><?php echo empty($Diag_did_Name) ? $empty_str : '{Diag_did_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaDiagDead" class="input-area"></div>
        </div>
        <div class="data-row-container"><div class="data-row">Аутопсия: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputAutopsyPerformType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{AutopsyPerformType_id}'><?php echo empty($AutopsyPerformType_id_Name) ? $empty_str : '{AutopsyPerformType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaAutopsyPerformType" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Результат аутопсии применительно к данной опухоли: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorAutopsyResultType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorAutopsyResultType_id}'><?php echo empty($TumorAutopsyResultType_id_Name) ? $empty_str : '{TumorAutopsyResultType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorAutopsyResultType" class="input-area"></div></div>
	</div>

	<?php if (getRegionNick() != 'kz') { ?>
	{OnkoConsult}<!-- «Сведения о проведении консилиума»-->
	<?php } ?>
    <?php if (getRegionNick() != 'kz') { ?>
	{DrugTherapyScheme}<!-- «Схема лекарственной терапии»-->
	<?php } ?>
	{MorbusOnkoDrug}

	{MorbusOnkoSpecTreat}<!-- «Специальное лечение»-->

	{MorbusOnkoRefusal}<!-- «Данные об отказах / противопоказаниях»-->
	
	<div class="toolbar">
		<a id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_copyEvnUsluga" class="button icon icon-copy16" title="Скопировать из случая лечения"><span>&nbsp;Скопировать из случая лечения</span></a>
	</div>
	
	{MorbusOnkoChemTer}<!--«Химиотерапевтическое лечение»-->
	{MorbusOnkoRadTer}<!--«Лучевое лечение»-->
	{MorbusOnkoGormTer}<!--«Гормоноиммунотерапевтическое лечение»-->
	{MorbusOnkoHirTer}<!--«Хирургическое лечение»-->
	{MorbusOnkoNonSpecTer}<!--«Неспецифическое лечение»-->

	{MorbusOnkoEvnNotify}<!--«Извещения»-->
	{MorbusOnkoPersonDisp}<!--«Диспансерная карта»-->

    <!-- «Контроль состояния»-->
    <div style="display: <?php echo (true) ? 'block' : 'none'; ?>; clear: both;">
        <div class="caption"><h2><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayControl" class="collapsible">Контроль состояния</span></h2></div>
        <div id="MorbusOnkoControl_{MorbusOnko_pid}_{Morbus_id}" style="display: block; padding:0px 5px 25px; border: 1px solid #000;">
            {MorbusOnkoBasePersonState}<!--«Общее состояние пациента»-->
            <div class="data-row-container"><div class="data-row">Клиническая группа: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoStatusYearEndType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoStatusYearEndType_id}'><?php echo empty($OnkoStatusYearEndType_id_Name) ? $empty_str : '{OnkoStatusYearEndType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoStatusYearEndType" class="input-area"></div></div>
			<?php if (!empty($isPalliatIncluded) && $isPalliatIncluded == true) { ?><div class="data-row-container"><div class="data-row">Пациент включен в регистр по паллиативной помощи {isPalliatIncluded}</div></div><?php } ?>
        </div>
    </div>

    {MorbusOnkoBasePS}<!-- «Госпитализация»-->

</div>



