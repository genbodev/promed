<?php
	$empty_str = '<span class="empty">Не указано</span>';
	$is_allow_edit = (1 == $accessType);
	$is_registry = ($Person_id == $MorbusNephro_pid);
	$isUfa = getRegionNick() == 'ufa';
	$resultTypeField = ($isUfa) ? 'Текущий статус:' : 'Исход наблюдения';
	$dialDateField   = ($isUfa) ? 'Дата начала:' : 'Дата:';
	$transplantField = ($isUfa) ? 'Дата трансплантации:' : 'Дата:';
	$lpuField = ($isUfa && $Lpu_id) ? 'Открепить' : 'Прикрепить';
?>
<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}" class="specifics">
	<div style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
		<!-- Диагноз (Регистр)-->
		{EvnDiagNephro}
	</div>
	<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_toolbarMorbusNephro" class="toolbar" style="display: none">
		<a id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_saveMorbusNephro" class="button icon icon-save16" title="Сохранить"><span></span></a>
	</div>
	<div class="data-row-container"><div class="data-row">Давность заболевания до установления диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_firstDate" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_firstDate) ? $empty_str : '{MorbusNephro_firstDate}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_firstDate" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Способ установления диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputNephroDiagConfType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($NephroDiagConfType_id) ? $empty_str : '{NephroDiagConfType_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaNephroDiagConfType" class="input-area"></div></div>
	<div class="data-row-container" style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;"><div class="data-row">Способ подтверждения диагноза: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputNephroDiagConfTypeC" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($NephroDiagConfType_cid) ? $empty_str : '{NephroDiagConfType_cName}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaNephroDiagConfTypeC" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Стадия ХБП: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputNephroCRIType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($NephroCRIType_id) ? $empty_str : '{NephroCRIType_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaNephroCRIType" class="input-area"></div></div>

	<div class="data-row-container" style="display: <?php echo ($is_registry)?'none':'block'; ?>;"><div class="data-row">Артериальная гипертензия: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_IsHyperten" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_IsHyperten) ? $empty_str : '{IsHyperten_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_IsHyperten" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Рост (в см): <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputPersonHeight" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonHeight_Height) ? $empty_str : '{PersonHeight_Height}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaPersonHeight" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row">Вес (в кг): <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputPersonWeight" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($PersonWeight_Weight) ? $empty_str : '{PersonWeight_Weight}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaPersonWeight" class="input-area"></div></div>
	<div class="data-row-container" style="display: <?php echo ($is_registry)?'none':'block'; ?>;"><div class="data-row">Назначенное лечение (диета, препараты): <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_Treatment" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_Treatment) ? $empty_str : '{MorbusNephro_Treatment}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_Treatment" class="input-area"></div></div>
	<div class="data-row-container" style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;"><div class="data-row">Дата постановки на учет: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_begDate" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_begDate) ? $empty_str : '{MorbusNephro_begDate}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_begDate" class="input-area"></div></div>
	<div class="data-row-container" style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;"><div class="data-row">Группа диспансерного учета: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputDispGroupType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($DispGroupType_id) ? $empty_str : '{DispGroupType_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaDispGroupType" class="input-area"></div></div>

	<div style="display: <?php echo (getRegionNick() != 'perm')?'none':'block'; ?>;">
		<!-- Нуждается в диализе -->
		{MorbusNephroDialysis}
	</div>

	<!-- #135648 -->
	<?php if($isUfa) { ?>

		<div style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
			{NephroCommission}
		</div>

		<div class="data-row-container" style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
			<div class="data-row">Диализный центр прикрепления: 
				<?php
					echo "<span id='MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_AttachLpu'>".(empty($Lpu_id) ? '<font color="#666">'.$empty_str.'</font>' : '<b>'.$Lpu_Nick.'</b>').'</span>';
					echo "&nbsp;&nbsp;&nbsp;&nbsp;";
					if($is_allow_edit && ($_SESSION['lpu_id'] == $Lpu_id || empty($Lpu_id)))
						echo "<span id='MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputAttachLpu' class='value link'>{$lpuField}</span>";
					
				?>
			</div>
			<!--div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaLpu" class="input-area"></div-->
		</div>

		<div class="data-row-container">
			<div class="data-row">Удаленность места жительства от ДЦ (км): 
				<?php
					if ($is_allow_edit)
						echo '<span id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_DistanceToDialysisCenter" class="value link">';
					else
						echo '<span class="value">';
					echo (empty($MorbusNephro_DistanceToDialysisCenter) ? $empty_str : '{MorbusNephro_DistanceToDialysisCenter}').'</span>'; 
				?>
			</div>
			<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_DistanceToDialysisCenter" class="input-area"></div>
		</div>

	<?php } ?>

	<div class="data-row-container" style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;"><div class="data-row"><?php echo $resultTypeField; ?> <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputNephroResultType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($NephroResultType_id) ? $empty_str : '{NephroResultType_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaNephroResultType" class="input-area"></div></div>

	<!-- #135648 -->
	<?php if($isUfa) { ?>

		<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_NephroPersonStatus" class="data-row-container" 
		style="display: <?php echo ( $is_registry && ($NephroResultType_Code==2 || $NephroResultType_Code==3))?'block':'none'; ?>;">
			<div class="data-row">Статус пациента: 
				<?php 
					if ($is_allow_edit) 
						echo '<span id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputNephroPersonStatus" class="value link">'; 
					else 
						echo '<span class="value">'; 
					echo (empty($NephroPersonStatus_id) ? $empty_str : '{NephroPersonStatus_Name}').'</span>';
				?>
			</div>
			<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaNephroPersonStatus" class="input-area"></div>
		</div>

		<div class="data-row-container" 
			id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_RoutineMonitoring" 
			style="display:<?php echo ($is_registry && ($NephroResultType_Code==1))?'block':'none'?>;">

			<div class="data-row">Дата с: 
				<?php
					if ($is_allow_edit)
						echo '<span id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_MonitoringBegDate" class="value link">';
					else
						echo '<span class="value">';
					echo (empty($MorbusNephro_MonitoringBegDate) ? $empty_str : '{MorbusNephro_MonitoringBegDate}').'</span>'; 
				?>
			</div>
			<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_MonitoringBegDate" class="input-area"></div>

			<div class="data-row" style="padding-right: 10px">&nbsp;&nbsp;&nbsp;&nbsp;Дата по: 
				<?php
					if ($is_allow_edit)
						echo '<span id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_MonitoringEndDate" class="value link">';
					else
						echo '<span class="value">';
					echo (empty($MorbusNephro_MonitoringEndDate) ? $empty_str : '{MorbusNephro_MonitoringEndDate}').'</span>'; 
				?>
			</div>
			<div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_MonitoringEndDate" class="input-area"></div>
		</div>

		<div
		 id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_NephroAccess"
		 style="display: <?php echo ( $is_registry && ($NephroResultType_Code==2 || $NephroResultType_Code==3))?'block':'none'; ?>;">
			{NephroAccess}
		</div>

	<?php } ?>

<fieldset class="clear" style="display: <?php
echo (false == $is_registry)?'none':'block';
?>; padding: 5px;">
<legend>Диализ</legend>
	<div class="data-row-container"><div class="data-row">Тип: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputDialysisType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($DialysisType_id) ? $empty_str : '{DialysisType_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaDialysisType" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row"><?php echo $dialDateField; ?> <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_dialDate" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_dialDate) ? $empty_str : '{MorbusNephro_dialDate}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_dialDate" class="input-area"></div></div>
	<?php if($isUfa) { ?>
		<div class="data-row-container"><div class="data-row">Дата окончания: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_dialEndDate" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_dialEndDate) ? $empty_str : '{MorbusNephro_dialEndDate}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_dialEndDate" class="input-area"></div></div>
	<?php } ?>
</fieldset>
<fieldset class="clear" style="display: <?php
echo (false == $is_registry)?'none':'block';
?>; padding: 5px;">
<legend>Трансплантация почки</legend>
	<div class="data-row-container"><div class="data-row">Тип: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputKidneyTransplantType" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($KidneyTransplantType_id) ? $empty_str : '{KidneyTransplantType_Name}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaKidneyTransplantType" class="input-area"></div></div>
	<div class="data-row-container"><div class="data-row"><?php echo $transplantField; ?> <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_transDate" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_transDate) ? $empty_str : '{MorbusNephro_transDate}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_transDate" class="input-area"></div></div>
	<?php if($isUfa) { ?>
		<div class="data-row-container"><div class="data-row">Дата отторжения трансплантата: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_transRejectDate" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_transRejectDate) ? $empty_str : '{MorbusNephro_transRejectDate}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_transRejectDate" class="input-area"></div></div>
	<?php } ?>
</fieldset>
	<div class="data-row-container" style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;"><div class="data-row">Дата смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputMorbusNephro_deadDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusNephro_deadDT) ? $empty_str : '{MorbusNephro_deadDT}'; ?></span></div><div id="MorbusNephro_{MorbusNephro_pid}_{MorbusNephro_id}_inputareaMorbusNephro_deadDT" class="input-area"></div></div>
	<div style="display: <?php echo ($is_registry)?'none':'block'; ?>;">
		<!-- Лабораторные исследования (Посещение) -->
		{MorbusNephroLab}
	</div>
	<div style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
		<!-- Динамическое наблюдение (Регистр) -->
		{MorbusNephroDisp}
	</div>
	<div style="display: <?php echo (false == $is_registry) ? 'none' : 'block'; ?>;">
		{MorbusNephroDrug}
	</div>

	<!-- #135648 -->
	<?php if($isUfa) { ?>
		<div style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
			<!-- Результаты услуг креатинин крови -->
			{NephroBloodCreatinine}
		</div>
		<div style="display: <?php echo (false == $is_registry)?'none':'block'; ?>;">
			<!-- Файлы -->
			{NephroDocument}
		</div>
	<?php } ?>
	<div class="clear"><br></div>
</div>