<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
?>
<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}" class="specifics">
<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_morbusPrint" class="button icon icon-print16" title="Печать"><span></span></a>
	<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbarMorbusTub" class="toolbar" style="display: none">
		<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_saveMorbusTub" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>

<?php if (!empty($isAllowPersonResidenceType)) { ?>
    <div class="data-row-container"><div class="data-row">Статус пациента: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputPersonResidenceType" class="value link" dataid="{PersonResidenceType_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonResidenceType_id) ? $empty_str : '{PersonResidenceType_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaPersonResidenceType" class="input-area"></div></div>
<?php } ?>
    <div class="data-row-container"><div class="data-row">Декретированная группа: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputPersonDecreedGroup" class="value link" dataid="{PersonDecreedGroup_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonDecreedGroup_id) ? $empty_str : '{PersonDecreedGroup_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaPersonDecreedGroup" class="input-area"></div></div>
    <div class="data-row-container"><div class="data-row">Жилищные условия: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputPersonLivingFacilies" class="value link" dataid="{PersonLivingFacilies_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonLivingFacilies_id) ? $empty_str : '{PersonLivingFacilies_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaPersonLivingFacilies" class="input-area"></div></div>
    <div class="data-row-container"><div class="data-row">Диагноз МКБ-10: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputDiag" class="value link" dataid="{Diag_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($Diag_id) ? $empty_str : '{Diag_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaDiag" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Клиническая форма: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubDiag" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubDiag_id) ? $empty_str : '{TubDiag_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubDiag" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Фаза: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubPhase" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubPhase_id) ? $empty_str : '{TubPhase_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubPhase" class="input-area"></div></div>
	{MorbusTubDiagGeneralForm}<!-- Раздел «Генерализованные формы» -->
	
	<h2>Сопутствующие заболевания</h2>
	<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbarMorbusTub5" class="toolbar" style="display: none">
		<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_saveMorbusTub5" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%"> 
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag1" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag1) ? 1 : '{SopDiag1}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag1) && $SopDiag1 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag1" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Сахарный диабет</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%"> 
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag2" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag2) ? 1 : '{SopDiag2}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag2) && $SopDiag2 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag2" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>ХНЗЛ</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag3" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag3) ? 1 : '{SopDiag3}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag3) && $SopDiag3 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag3" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Гипертоническая болезнь, ИБС</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag4" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag4) ? 1 : '{SopDiag4}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag4) && $SopDiag4 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag4" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Язвенная болезнь желудка и 12 перстной кишки</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag5" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag5) ? 1 : '{SopDiag5}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag5) && $SopDiag5 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag5" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Психическое заболевание</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag6" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag6) ? 1 : '{SopDiag6}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag6) && $SopDiag6 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag6" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Онкологическое заболевание</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag8" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag8) ? 1 : '{SopDiag8}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag8) && $SopDiag8 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag8" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>ВИЧ</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag7" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($SopDiag7) ? 1 : '{SopDiag7}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($SopDiag7) && $SopDiag7 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag7" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Прочее (указать какое)</div>
			<span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputSopDiag_Descr" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($SopDiag_Descr) ? $empty_str : '{SopDiag_Descr}'; ?></span>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaSopDiag_Descr" class="input-area"></div>
		</div>
	</div>
	
	<div class="data-table" style="width:100%;float:left;"></div>
	<h2 style="width:100%;float:left;">Факторы риска</h2>
	<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbarMorbusTub6" class="toolbar" style="display: none">
		<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_saveMorbusTub6" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%"> 
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType1" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType1) ? 1 : '{RiskType1}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType1) && $RiskType1 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType1" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Нахождение в МЛС</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%"> 
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType2" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType2) ? 1 : '{RiskType2}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType2) && $RiskType2 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType2" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Контакт с больным туберкулезом</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType3" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType3) ? 1 : '{RiskType3}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType3) && $RiskType3 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType3" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Наркологическая зависимость</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType4" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType4) ? 1 : '{RiskType4}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType4) && $RiskType4 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType4" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Алкогольная зависимость</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType5" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType5) ? 1 : '{RiskType5}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType5) && $RiskType5 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType5" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Роды</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType6" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType6) ? 1 : '{RiskType6}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType6) && $RiskType6 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType6" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Социальная дезадаптация</div>
		</div>
	</div>
	<div class="data-row-container">
		<div class="data-row" style="width:100%">
			<input id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputRiskType8" style="float:left;margin-right:2px;margin-top:2px;" type="checkbox" dataid="<?php echo empty($RiskType8) ? 1 : '{RiskType8}'; ?>" <?php echo ($is_allow_edit) ? '' : 'disabled' ;?> <?php echo ((!empty($RiskType8) && $RiskType8 == 2) ? 'checked' : ''); ?>>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaRiskType8" style="float:left;margin-right:2px;margin-top:2px;" class="input-area"></div>
			<div>Получающие кортикостероидную, лучевую, цитостатическую терапию</div>
		</div>
	</div>
	
	<div class="data-table" style="width:100%;float:left;height:1px;"></div>
	<div style="width:100%;float:left;">
		<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbarMorbusTub2" class="toolbar" style="display: none">
			<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_saveMorbusTub2" class="button icon icon-save16" title="Сохранить"><span></span></a>
		</div>
		<div class="data-row-container"><div class="data-row">Группа больных: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubSickGroupType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubSickGroupType_id) ? $empty_str : '{TubSickGroupType_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubSickGroupType" class="input-area"></div></div>

		<!--div class="data-row-container"><div class="data-row">Региональный регистрационный номер пациента: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_RegNumCard" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_RegNumCard) ? $empty_str : '{MorbusTub_RegNumCard}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_RegNumCard" class="input-area"></div></div-->
		<div class="data-row-container"><div class="data-row">Дата возникновения симптомов: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_begDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_begDT) ? $empty_str : '{MorbusTub_begDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_begDT" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Дата первого обращения к любому врачу по поводу этих симптомов: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_FirstDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_FirstDT) ? $empty_str : '{MorbusTub_FirstDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_FirstDT" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Дата установления диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_DiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_DiagDT) ? $empty_str : '{MorbusTub_DiagDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_DiagDT" class="input-area"></div></div>
	</div>

	{MorbusTubConditChem}<!--«Режими химиотерапии»-->
	{MorbusTubPrescr}<!--«Лекарственные назначения»-->
	{MorbusTubStudyResult}<!--«Результаты исследований»-->
	<!-- Раздел «Оперативное лечение» -->
	<div class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbar').style.display='none'">
		<div class="caption">
			<h2><span id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toggleDisplayMorbusTubAdviceList" class="<?php if (!empty($MorbusTubAdvice)) { ?>collapsible<?php } ?>">Оперативное лечение</span></h2>
			<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbar" class="toolbar">
				<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_addMorbusTubAdvice" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display:none;"'; } ?>><span></span></a>
			</div>
		</div>

		<div id="MorbusTubAdviceItems_{MorbusTub_pid}_{MorbusTub_id}" style="display: <?php echo (empty($MorbusTubAdvice))?'none':'block'; ?>;">
		   {MorbusTubAdvice}
		</div>
	</div>
	{EvnDirectionTub}<!--Направление на проведение микроскопических исследований на туберкулез-->
	


	<div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbarMorbusTub3" class="toolbar" style="display: none">
		<a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_saveMorbusTub3" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>
	<div class="data-row-container"><div class="data-row">Исход курса химиотерапии: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubResultChemClass" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubResultChemClass_id) ? $empty_str : '{TubResultChemClass_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubResultChemClass" class="input-area"></div></div>
	
	
		<div class="data-row-container"><div class="data-row">Тип подтверждения: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubResultChemType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubResultChemType_id) ? $empty_str : '{TubResultChemType_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubResultChemType" class="input-area"></div></div>
		<div class="data-row-container"><div class="data-row">Дата исхода курса химиотерапии: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_ResultDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_ResultDT) ? $empty_str : '{MorbusTub_ResultDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_ResultDT" class="input-area"></div></div>
	</div>


	<div class="data-row-container"><div class="data-row">Дата смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_deadDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_deadDT) ? $empty_str : '{MorbusTub_deadDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_deadDT" class="input-area"></div></div>	
	<div class="data-row-container"><div class="data-row">Причина смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubResultDeathType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubResultDeathType_id) ? $empty_str : '{TubResultDeathType_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubResultDeathType" class="input-area"></div></div>
			
	<div class="data-row-container"><div class="data-row">Дата прерывания курса химиотерапии: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_breakDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_breakDT) ? $empty_str : '{MorbusTub_breakDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_breakDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Причина прерывания химиотерапии: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubBreakChemType" class="value link" dataid="{TubBreakChemType_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubBreakChemType_id) ? $empty_str : '{TubBreakChemType_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubBreakChemType" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Выбыл: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_disDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_disDT) ? $empty_str : '{MorbusTub_disDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_disDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Группа диспансерного наблюдения: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputPersonDispGroup" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonDispGroup_id) ? $empty_str : '{PersonDispGroup_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaPersonDispGroup" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Дата завершения санаторно-курортного лечения: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_SanatorDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_SanatorDT) ? $empty_str : '{MorbusTub_SanatorDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_SanatorDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Дата перевода в III группу ДУ: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_ConvDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_ConvDT) ? $empty_str : '{MorbusTub_ConvDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_ConvDT" class="input-area"></div></div>
	
	<div class="data-row-container"><div class="data-row">Дата снятия диагноза туберкулеза: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_unsetDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_unsetDT) ? $empty_str : '{MorbusTub_unsetDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_unsetDT" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Общее кол-во дней нетрудоспособности: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTub_CountDay" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTub_CountDay) ? $empty_str : '{MorbusTub_CountDay}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTub_CountDay" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Инвалидность: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputTubDisability" class="value link" dataid="{$TubDisability_id}"<?php } else { echo ' class="value"';} ?>><?php echo empty($TubDisability_id) ? $empty_str : '{TubDisability_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaTubDisability" class="input-area"></div></div>

	<!-- Раздел «Химиотерапия по IV режиму лечения (МЛУ)» -->
<?php if (!empty($isAllowMorbusTubMDR)) { ?>
	<div class="data-table">
	    <div class="caption">
	        <h2><span id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toggleDisplayMorbusTubMDR" class="collapsible">Химиотерапия по IV режиму лечения (МЛУ)</span></h2>
	        <div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_toolbarMorbusTub4" class="toolbar" style="display: none">
	            <a id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_saveMorbusTub4" class="button icon icon-save16" title="Сохранить"><span></span></a>
	        </div>
	    </div>
	    <div id="MorbusTubMDR_{MorbusTub_pid}_{MorbusTub_id}" style="display: none">
	        <div class="data-row-container"><div class="data-row">Региональный регистрационный номер пациента: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_RegNumPerson" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_RegNumPerson) ? $empty_str : '{MorbusTubMDR_RegNumPerson}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_RegNumPerson" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Региональный регистрационный номер случая лечения: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_RegNumCard" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_RegNumCard) ? $empty_str : '{MorbusTubMDR_RegNumCard}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_RegNumCard" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Дата регистрации ЦВКК: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_regDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_regDT) ? $empty_str : '{MorbusTubMDR_regDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_regDT" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Дата регистрации на лечение по IV режиму: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_regdiagDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_regdiagDT) ? $empty_str : '{MorbusTubMDR_regdiagDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_regdiagDT" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Дата первого обнаружения устойчивости к рифампицину: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_begDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_begDT) ? $empty_str : '{MorbusTubMDR_begDT}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_begDT" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Группа диспансерного наблюдения на момент регистрации текущего случая лечения: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_GroupDisp" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_GroupDisp) ? $empty_str : '{MorbusTubMDR_GroupDisp}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_GroupDisp" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Диагноз: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_TubDiag" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_TubDiag_id) ? $empty_str : '{MorbusTubMDR_TubDiag_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_TubDiag" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Группа случая лечения туберкулеза по IV режиму: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_TubSickGroupType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_TubSickGroupType_id) ? $empty_str : '{MorbusTubMDR_TubSickGroupType_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_TubSickGroupType" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Наличие патологии, кодируемой В20-В24: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsPathology" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsPathology) ? $empty_str : '{IsPathology_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsPathology" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Назначена АРТ: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsART" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsART) ? $empty_str : '{IsART_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsART" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Назначена профилактическая терапия котримоксазолом: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsCotrim" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsCotrim) ? $empty_str : '{IsCotrim_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsCotrim" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Проходил лечение препаратами 1-го ряда до начала текущего курса лечения: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsDrugFirst" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsDrugFirst) ? $empty_str : '{IsDrugFirst_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsDrugFirst" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Проходил лечение ранее препаратами 2-го ряда: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsDrugSecond" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsDrugSecond) ? $empty_str : '{IsDrugSecond_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsDrugSecond" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Курс лечения по IV режиму обоснован результатами тестов на лекарственную чувствительность: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsDrugResult" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsDrugResult) ? $empty_str : '{IsDrugResult_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsDrugResult" class="input-area"></div></div>
	        <div class="data-row-container"><div class="data-row">Начат, как эмпирический курс: <span<?php if ($is_allow_edit) { ?> id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputMorbusTubMDR_IsEmpiric" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusTubMDR_IsEmpiric) ? $empty_str : '{IsEmpiric_Name}'; ?></span></div><div id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_inputareaMorbusTubMDR_IsEmpiric" class="input-area"></div></div>

            {MorbusTubMDRStudyResult}<!--Результаты исследований-->
            {MorbusTubMDRPrescr}<!--Лечебные мероприятия-->
	    </div>
        <div id="MorbusTubMDR_{MorbusTub_pid}_{MorbusTub_id}_printWrap" style="display: <?php echo (empty($MorbusTubMDR_id))?'none':'block'; ?>">
	        <span id="MorbusTub_{MorbusTub_pid}_{MorbusTub_id}_printLink" class="link">Медицинская карта по форме №01-МЛУ-ТБ/у</span>
        </div>
	</div>
<?php } ?>
	<div class="clear"><br></div>
</div>



