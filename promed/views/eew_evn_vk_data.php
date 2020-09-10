<?php
	function getHeader() {

	}
	function get($var, $empty_str = '<span style="color: #666;">Не указано</span>') {
		return empty($var)?$empty_str:$var;
	}
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $accessType);
?>
<div id="EvnVK_data_{EvnVK_id}" class="columns">
	<div class="left">
		<div id="EvnVK_data_{EvnVK_id}_content" class="content">

			<div class="data-row-container"><div class="data-row">Протокол заседания ВК номер: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputNumProtocol" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_NumProtocol}'><?php echo empty($EvnVK_NumProtocol) ? $empty_str : '{EvnVK_NumProtocol}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaNumProtocol" class="input-area"></div></div>

			<div class="caption"><h2>Общие данные</h2></div>
			<div class="data-row-container">
				<div class="data-row">Дата экспертизы: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputSetDate" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_setDate}'><?php echo empty($EvnVK_setDate) ? $empty_str : '{EvnVK_setDate}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaSetDate" class="input-area"></div>
				<div class="data-row" style="margin-left: 5px;">Зарезервировано: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputIsReserve" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_isReserve}'><?php echo empty($EvnVK_isReserveYN) ? $empty_str : '{EvnVK_isReserveYN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaIsReserve" class="input-area"></div>
			</div>
			<div class="data-row-container"><div class="data-row">Врач, направивший на ВК: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputMedPersonal" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MedPersonal_id}'><?php echo empty($MedPersonal_Fio) ? $empty_str : '{MedPersonal_Fio}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaMedPersonal" class="input-area"></div></div>

			<div class="caption"><h2>Причина обращения и диагнозы</h2></div>
			<div class="data-row-container"><div class="data-row">Номер КВС(ТАП): <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputNumCard" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_NumCard}'><?php echo empty($EvnVK_NumCard) ? $empty_str : '{EvnVK_NumCard}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaNumCard" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Статус пациента: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputPatientStatusType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{PatientStatusType_id}'><?php echo empty($PatientStatusType_Name) ? $empty_str : '{PatientStatusType_Name}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaPatientStatusType" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Профессия пациента: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputProf" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_Prof}'><?php echo empty($EvnVK_Prof) ? $empty_str : '{EvnVK_Prof}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaProf" class="input-area"></div></div>

			<div class="caption"><h2>Пациент</h2></div>
			<div class="data-row-container"><div class="data-row">Причина обращения: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputCauseTreatmentType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CauseTreatmentType_id}'><?php echo empty($CauseTreatmentType_id) ? $empty_str : '{CauseTreatmentType_Name}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaCauseTreatmentType" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Диагноз основной: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputDiag" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Diag_id}'><?php echo empty($Diag_id) ? $empty_str : '{Diag_Code} {Diag_Name}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaDiag" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Основное заболевание: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputMainDisease" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_MainDisease}'><?php echo empty($EvnVK_MainDisease) ? $empty_str : '{EvnVK_MainDisease}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaMainDisease" class="input-area"></div></div>
			<?php if(getRegionNumber() != 59){ ?><div class="data-row-container"><div class="data-row">Диагноз сопутствующий: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputDiagS" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{Diag_sid}'><?php echo empty($Diag_sid) ? $empty_str : '{Diag_sCode} {Diag_sName}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaDiagS" class="input-area"></div></div><?php } ?>
			
			<?php if(getRegionNumber() == 59) { ?>
			{EvnVKSopDiag}
			{EvnVKOslDiag}
			<?php } ?>
			
			<div class="caption"><h2>Экспертиза</h2></div>
			<div class="data-row-container"><div class="data-row">Вид экспертизы: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputExpertiseNameType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ExpertiseNameType_id}'><?php echo empty($ExpertiseNameType_id) ? $empty_str : '{ExpertiseNameType_Name}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaExpertiseNameType" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Хар-ка случая экспертизы: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputExpertiseEventTypeLink" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ExpertiseEventTypeLink_id}'><?php echo empty($ExpertiseEventTypeLink_Name) ? $empty_str : '{ExpertiseEventTypeLink_Name}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaExpertiseEventTypeLink" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Предмет экспертизы: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputExpertiseNameSubjectType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{ExpertiseNameSubjectType_id}'><?php echo empty($ExpertiseNameSubjectType_Name) ? $empty_str : '{ExpertiseNameSubjectType_Name}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaExpertiseNameSubjectType" class="input-area"></div></div>

			<div class="caption"><h2>Нетрудоспособность</h2></div>
			<div class="data-row-container"><div class="data-row">ЛВН: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputEvnStickAll" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnStick_all}'><?php echo empty($EvnStick_all) ? $empty_str : '{EvnStick_all}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaEvnStickAll" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">ЛВН (ручной ввод): <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputLVN" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_LVN}'><?php echo empty($EvnVK_LVN) ? $empty_str : '{EvnVK_LVN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaLVN" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Период освобождения от работы: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputEvnStickWorkRelease" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnStickWorkRelease_id}'><?php echo empty($EvnStickWorkRelease_info) ? $empty_str : '{EvnStickWorkRelease_info}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaEvnStickWorkRelease" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Период освобождения от работы (ручной ввод): <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputWorkReleasePeriod" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_WorkReleasePeriod}'><?php echo empty($EvnVK_WorkReleasePeriod) ? $empty_str : '{EvnVK_WorkReleasePeriod}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaWorkReleasePeriod" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Эксперитза временной нетрудоспособности №: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputExpertiseStickNumber" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ExpertiseStickNumber}'><?php echo empty($EvnVK_ExpertiseStickNumber) ? $empty_str : '{EvnVK_ExpertiseStickNumber}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaExpertiseStickNumber" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Срок нетрудоспособности, дней: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputStickPeriod" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_StickPeriod}'><?php echo empty($EvnVK_StickPeriod) ? $empty_str : '{EvnVK_StickPeriod}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaStickPeriod" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Длительность пребывания в ЛПУ, дней: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputStickDuration" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_StickDuration}'><?php echo empty($EvnVK_StickDuration) ? $empty_str : '{EvnVK_StickDuration}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaStickDuration" class="input-area"></div></div>

			<?php if (!empty($EvnPrescrMse_id)) { ?>
			<div class="caption"><h2>Медико-социальная экспертиза</h2></div>
			<div class="data-row-container"><div class="data-row">Дата направления в бюро МСЭ (или др. спец. учреждения): <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputDirectionDate" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_DirectionDate}'><?php echo empty($EvnVK_DirectionDate) ? $empty_str : '{EvnVK_DirectionDate}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaDirectionDate" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Дата получения заключения МСЭ (или др. спец. учреждений): <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputConclusionDate" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ConclusionDate}'><?php echo empty($EvnVK_ConclusionDate) ? $empty_str : '{EvnVK_ConclusionDate}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaConclusionDate" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Cрок действия заключения: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputConclusionPeriodDate" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ConclusionPeriodDate}'><?php echo empty($EvnVK_ConclusionPeriodDate) ? $empty_str : '{EvnVK_ConclusionPeriodDate}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaConclusionPeriodDate" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Заключение МСЭ: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputConclusionDescr" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ConclusionDescr}'><?php echo empty($EvnVK_ConclusionDescr) ? $empty_str : '{EvnVK_ConclusionDescr}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaConclusionDescr" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Доп. информация: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputAddInfo" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_AddInfo}'><?php echo empty($EvnVK_AddInfo) ? $empty_str : '{EvnVK_AddInfo}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaAddInfo" class="input-area"></div></div>
			<?php } ?>

			<div class="caption"><h2>Стандарты, дефекты, результаты, заключения</h2></div>
			<?php if(getRegionNumber() != 101) { ?>
			<div class="data-row-container"><div class="data-row">Использовались стандарты: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputIsUseStandard" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_isUseStandard}'><?php echo empty($EvnVK_isUseStandardYN) ? $empty_str : '{EvnVK_isUseStandardYN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaIsUseStandard" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Подробности: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputUseStandard" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_UseStandard}'><?php echo empty($EvnVK_UseStandard) ? $empty_str : '{EvnVK_UseStandard}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaUseStandard" class="input-area"></div></div>
			<?php } ?>
			<div class="data-row-container"><div class="data-row">Отклонение от стандартов: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputIsAberration" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_isAberration}'><?php echo empty($EvnVK_isAberrationYN) ? $empty_str : '{EvnVK_isAberrationYN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaIsAberration" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Подробности: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputAberrationDescr" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_AberrationDescr}'><?php echo empty($EvnVK_AberrationDescr) ? $empty_str : '{EvnVK_AberrationDescr}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaAberrationDescr" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Дефекты, нарушения и ошибки: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputIsErrors" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_isErrors}'><?php echo empty($EvnVK_isErrorsYN) ? $empty_str : '{EvnVK_isErrorsYN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaIsErrors" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Подробности: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputErrorsDescr" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ErrorsDescr}'><?php echo empty($EvnVK_ErrorsDescr) ? $empty_str : '{EvnVK_ErrorsDescr}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaErrorsDescr" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Достижение результата или исхода: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputIsResult" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_isResult}'><?php echo empty($EvnVK_isResultYN) ? $empty_str : '{EvnVK_isResultYN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaIsResult" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Подробности: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputResultDescr" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ResultDescr}'><?php echo empty($EvnVK_ResultDescr) ? $empty_str : '{EvnVK_ResultDescr}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaResultDescr" class="input-area"></div></div>
			<div class="data-row-container"><div class="data-row">Заключ. экспертов, рекомендации: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputExpertDescr" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_ExpertDescr}'><?php echo empty($EvnVK_ExpertDescr) ? $empty_str : '{EvnVK_ExpertDescr}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaExpertDescr" class="input-area"></div></div>
			
			<?php if(getRegionNick() == 'vologda') { ?> 
			<div class="data-row-container"><div class="data-row">Решение комиссии: <span class="value" dataid='{EvnVK_isAccepted}'><?php echo empty($EvnVK_isAcceptedYN) ? $empty_str : '{EvnVK_isAcceptedYN}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaIsAccepted" class="input-area"></div></div>
			<?php } ?>

			<div class="data-row-container"><a id="EvnVK_{EvnVK_id}_selectDecisionVK" class="button" style="margin: 5px 0 0;" title="Выбор шаблона решения ВК"><span>Выбор шаблона решения ВК</span></a></div>
			<div class="data-row-container"><div class="data-row">Описание решения ВК: <span<?php if ($is_allow_edit) { ?> id="EvnVK_{EvnVK_id}_inputDecisionVK" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{EvnVK_DecisionVK}'><?php echo empty($EvnVK_DecisionVK) ? $empty_str : '<br/>{EvnVK_DecisionVK}'; ?></span></div><div id="EvnVK_{EvnVK_id}_inputareaDecisionVK" class="input-area" style="clear: both;"></div></div>

			{EvnVKExpert}

		</div>
	</div>
	<div class="right">
		<div id="EvnVK_{EvnVK_id}_toolbar" class="toolbar" style="display: none">
			<a id="EvnVK_{EvnVK_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
		</div>
	</div>
</div>