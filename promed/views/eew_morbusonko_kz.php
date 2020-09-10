<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      01.2017
 */
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = ('edit' == $accessType);
?>
<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}" class="specifics">
	<div class="caption">
		<h2><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayDiag" class="collapsible">Диагноз</span></h2>
		<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toolbarDiag" class="toolbar" style="display: none">
			<a id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_saveDiag" class="button icon icon-save16" title="Сохранить"><span></span></a>
		</div>
	</div>
	<div id="MorbusOnkoDiag_{MorbusOnko_pid}_{Morbus_id}" style="display: <?php echo (empty($MorbusOnko_NumTumor))?'none':'block'; ?>; padding:0px 5px 25px; border: 1px solid #000;">
		<div class="data-row-container">
			<div class="data-row">МТН (РМН): 
				<span class="value">
					<?php echo empty($MorbusOnkoBase_NumCard) ? $empty_str : '{MorbusOnkoBase_NumCard}'; ?>
				</span>
			</div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Қорытынды диагноз (Заключительный диагноз): 
				<span
				<?php if ($is_allow_edit) { ?> 
				id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputEndDiag" class="value link"
				<?php } else { echo ' class="value"';} ?>
				dataid='{Diag_id}'>
					<?php echo empty($Diag_id_Name) ? $empty_str : '{Diag_id_Name}'; ?>
				</span>
			</div>
			<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaEndDiag" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Осы ауру туралы МҰ алғашқы жолдама (Первое обращение в МО по данному заболеванию). Күні (Дата): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputfirstVizitDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_firstVizitDT) ? $empty_str : '{MorbusOnko_firstVizitDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareafirstVizitDT" class="input-area"></div>
			<div class="data-row seq">МҰ (МО): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputLpu" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Lpu_foid}'><?php echo empty($Lpu_foid_Name) ? $empty_str : '{Lpu_foid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaLpu" class="input-area"></div>
		</div>
		<div class="data-row-container"><div class="data-row">Диагноз қойылған күн (Дата установления диагноза): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputsetDiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusOnko_setDiagDT) ? $empty_str : '{MorbusOnko_setDiagDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareasetDiagDT" class="input-area"></div></div>
		<div class="data-row-container">
			<div class="data-row">нұсқалықтық көрсету (указать вариантность): 
				<span
				<?php if ($is_allow_edit) { ?> 
				id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoVariance" class="value link"
				<?php } else { echo ' class="value"';} ?>
				>
					<?php echo empty($OnkoVariance_id_Name) ? $empty_str : '{OnkoVariance_id_Name}'; ?>
				</span>
			</div>
			<?php if(!empty($Diag_id_Name) && substr(trim($Diag_id_Name),0,3) >= 'C81' && substr(trim($Diag_id_Name),0,3) <= 'C96') { ?>
				<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoVariance" class="input-area"></div>
			<?php } ?>
		</div>
		<div class="data-row-container">
			<div class="data-row">Қауіп тобы (Группа риска): 
				<span
				<?php if ($is_allow_edit) { ?> 
				id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoRiskGroup" class="value link"
				<?php } else { echo ' class="value"';} ?>
				>
					<?php echo empty($OnkoRiskGroup_id_Name) ? $empty_str : '{OnkoRiskGroup_id_Name}'; ?>
				</span>
			</div>
			<?php if(!empty($Diag_id_Name) && substr(trim($Diag_id_Name),0,3) >= 'C81' && substr(trim($Diag_id_Name),0,3) <= 'C96') { ?>
				<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoRiskGroup" class="input-area"></div>
			<?php } ?>
		</div>
		<div class="data-row-container">
			<div class="data-row">Тиістілік (Резистентность): 
				<span
				<?php if ($is_allow_edit) { ?> 
				id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoResistance" class="value link"
				<?php } else { echo ' class="value"';} ?>
				>
					<?php echo empty($OnkoResistance_id_Name) ? $empty_str : '{OnkoResistance_id_Name}'; ?>
				</span>
			</div>
			<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoResistance" class="input-area"></div>
		</div>

		{MorbusOnkoSopDiag}<!-- «Сопутствующие заболевания»-->

		<div class="data-row-container"><div class="data-row">Iсiк топографиясы (Топография опухоли): <span class="value"><?php echo empty($Diag_id_Name) ? $empty_str : '{Diag_id_Name}'; ?></span></div></div>
		<div class="data-row-container"><div class="data-row">Iсiктiң морфологиялық түрi (Морфологический тип опухоли): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoDiag" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoDiag_mid}'><?php echo empty($OnkoDiag_mid_Name) ? $empty_str : '{OnkoDiag_mid_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoDiag" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Iсiк процесiнiң кезеңi (Стадия опухолевого процесса): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorStage" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorStage_id}'><?php echo empty($TumorStage_id_Name) ? $empty_str : '{TumorStage_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorStage" class="input-area"></div></div>
		<div class="data-row-container">
			<div class="data-row">TNM жүйесi бойынша iсiктiң таралуы (Распространенность опухоли по системе TNM). T: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoT" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoT_id}'><?php echo !isset($OnkoT_id_Name) ? $empty_str : '{OnkoT_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoT" class="input-area"></div>
			<div class="data-row seq">N: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoN" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoN_id}'><?php echo !isset($OnkoN_id_Name) ? $empty_str : '{OnkoN_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoN" class="input-area"></div>
			<div class="data-row seq">M: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoM" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoM_id}'><?php echo !isset($OnkoM_id_Name) ? $empty_str : '{OnkoM_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoM" class="input-area"></div>
		</div>

		<h2 style='clear:both;'><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayMeta" class="collapsible">Жырақ метастаздардың орналасуы (Локализация отдаленных метастазов)</span></h2>
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
		{confString}
		<div class="data-row-container"><div class="data-row">Iсiктiң анықталу жағдайы (Обстоятельства выявления опухоли): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorCircumIdentType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorCircumIdentType_id}'><?php echo empty($TumorCircumIdentType_id_Name) ? $empty_str : '{TumorCircumIdentType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorCircumIdentType" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Диагноздың кеш қойылу себептерi (Причины поздней диагностики): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoLateDiagCause" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoLateDiagCause_id}'><?php echo empty($OnkoLateDiagCause_id_Name) ? $empty_str : '{OnkoLateDiagCause_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoLateDiagCause" class="input-area"></div></div>
        <div class="data-row-container">
			<div class="data-row">Есепке алынған күні (Дата взятия на учет): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputMorbusBaseSetDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusBase_setDT) ? $empty_str : '{MorbusBase_setDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaMorbusBaseSetDT" class="input-area"></div>
			<div class="data-row seq">Есепке алынды (Взят на учет): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoRegType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoRegType_id}'><?php echo empty($OnkoRegType_id_Name) ? $empty_str : '{OnkoRegType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoRegType" class="input-area"></div>
		</div>
		<div class="data-row-container">
			<div class="data-row">Есепке клиникалық топпен алынды (Взят на учет с клинической группой): 
				<span
				<?php if ($is_allow_edit) { ?> 
				id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoStatusBegType" class="value link"
				<?php } else { echo ' class="value"';} ?>
				>
					<?php echo empty($OnkoStatusBegType_id_Name) ? $empty_str : '{OnkoStatusBegType_id_Name}'; ?>
				</span>
			</div>
			<div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoStatusBegType" class="input-area"></div>
		</div>
		<div class="data-row-container"><div class="data-row">Алғашқы-көптік iсiктер кезiнде (Первично-множественная опухоль): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputTumorPrimaryMultipleType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{TumorPrimaryMultipleType_id}'><?php echo empty($TumorPrimaryMultipleType_id_Name) ? $empty_str : '{TumorPrimaryMultipleType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaTumorPrimaryMultipleType" class="input-area"></div></div>
		<div class="data-row-container">
			<div class="data-row">Есептен шығарылды (Снят с учета): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputMorbusBaseDisDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusBase_disDT) ? $empty_str : '{MorbusBase_disDT}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaMorbusBaseDisDT" class="input-area"></div>
			<div class="data-row seq">Себебi (По причине): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoRegOutType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoRegOutType_id}'><?php echo empty($OnkoRegOutType_id_Name) ? $empty_str : '{OnkoRegOutType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoRegOutType" class="input-area"></div>
		</div>
		<div class="data-row-container">
            <div class="data-row">Қайтыс болу себебi (Причина смерти): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputDiagDead" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Diag_did}'><?php echo empty($Diag_did_Name) ? $empty_str : '{Diag_did_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaDiagDead" class="input-area"></div>
        </div>
        <div class="data-row-container"><div class="data-row">Аутопсия: <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputAutopsyPerformType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{AutopsyPerformType_id}'><?php echo empty($AutopsyPerformType_id_Name) ? $empty_str : '{AutopsyPerformType_id_Name}'; ?></span></div><div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaAutopsyPerformType" class="input-area"></div></div>
    </div>

    {MorbusOnkoSpecTreat}<!-- «Специальное лечение»-->
	{MorbusOnkoHirTer}<!--«Хирургическое лечение»-->
	{MorbusOnkoRadTer}<!--«Лучевое лечение»-->
	{MorbusOnkoChemTer}<!--«Химиотерапевтическое лечение»-->
	<!-- «Контроль состояния»-->
    <div style="display: <?php echo (true) ? 'block' : 'none'; ?>; clear: both;">
        <div class="caption"><h2><span id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_toggleDisplayControl" class="collapsible">Контроль состояния</span></h2></div>
        <div id="MorbusOnkoControl_{MorbusOnko_pid}_{Morbus_id}" style="display: block; padding:0px 5px 25px; border: 1px solid #000;">
            {MorbusOnkoBasePersonState}<!--«Общее состояние пациента»-->
            <div class="data-row-container"><div class="data-row">Клиника-лық топ (клиническая группа): <span<?php if ($is_allow_edit) { ?> id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoStatusYearEndType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{OnkoStatusYearEndType_id}'><?php echo empty($OnkoStatusYearEndType_id_Name) ? $empty_str : '{OnkoStatusYearEndType_id_Name}'; ?></span></div>
            <div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoStatusYearEndType" class="input-area"></div>
            <div class="data-row-container">
            	<div class="data-row">Инвалидность по основному заболеванию: <span <?php if ($is_allow_edit) { ?>id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputOnkoInvalidType" class="value link"<?php } else {echo ' class="value"';}?> dataid="{OnkoInvalidType_id}"><?php echo empty($OnkoInvalidType_id_Name) ? $empty_str : '{OnkoInvalidType_id_Name}'?></span></div>
            </div>
            <div id="MorbusOnko_{MorbusOnko_pid}_{Morbus_id}_inputareaOnkoInvalidType" class="input-area"></div>
           </div>
        </div>
    </div>

</div>