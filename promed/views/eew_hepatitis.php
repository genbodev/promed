<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ('edit' == $AccessType);
?>
<div id="MorbusHepatitis_{MorbusHepatitis_pid}_{MorbusHepatitis_id}" class="specifics">
	<?php echo (empty($MorbusHepatitisEvn)?'<div style=\'clear:both;\'>Посещения/госпитализации заболевания</div>':'{MorbusHepatitisEvn}'); ?>
	<div class="data-row-container"><div class="data-row">Уникальный номер регистровой записи: <?php
		echo ($is_allow_edit) ? '<span id="MorbusHepatitis_{MorbusHepatitis_pid}_{MorbusHepatitis_id}_MorbusHepatitis_id" dataid="{MorbusHepatitis_id}">' : '<span class="value">';
		echo empty($Morbus_id) ? $empty_str : '{MorbusHepatitis_id}';
		echo '</span></div>';

	?></div>
	<div class="data-row-container"><div class="data-row">Эпиданамнез: <?php
		echo ($is_allow_edit) ? '<span id="MorbusHepatitis_{MorbusHepatitis_pid}_{MorbusHepatitis_id}_inputEpidAns" class="value link" dataid="{HepatitisEpidemicMedHistoryType_id}">' : '<span class="value">';
		echo empty($HepatitisEpidemicMedHistoryType_Name) ? $empty_str : '{HepatitisEpidemicMedHistoryType_Name}';
		echo '</span></div>';
		echo ($is_allow_edit) ? '<div id="MorbusHepatitis_{MorbusHepatitis_pid}_{MorbusHepatitis_id}_inputareaEpidAns" class="input-area"></div>' : '';
	?></div>
	<div class="data-row-container"><div class="data-row">Эпидномер: <?php
		echo ($is_allow_edit) ? '<span id="MorbusHepatitis_{MorbusHepatitis_pid}_{MorbusHepatitis_id}_inputEpidNum" class="value link">' : '<span class="value">';
		echo empty($MorbusHepatitis_EpidNum) ? $empty_str : '{MorbusHepatitis_EpidNum}';
		echo '</span></div>';
		echo ($is_allow_edit) ? '<div id="MorbusHepatitis_{MorbusHepatitis_pid}_{MorbusHepatitis_id}_inputareaEpidNum" class="input-area"></div>' : '';
	?></div>
	<?php 
		echo (empty($MorbusHepatitisDiag)?'<div style=\'clear:both;\'>Диагноз</div>':'{MorbusHepatitisDiag}');
		echo (empty($MorbusHepatitisSopDiag)?'<div style=\'clear:both;\'>Сопутствующий диагноз</div>':'{MorbusHepatitisSopDiag}');
		/*
		echo (empty($MorbusHepatitisLabConfirm)?'<div style=\'clear:both;\'>Лабораторные подтверждения</div>':'{MorbusHepatitisLabConfirm}');
		echo (empty($MorbusHepatitisFuncConfirm)?'<div style=\'clear:both;\'>Инструментальные подтверждения</div>':'{MorbusHepatitisFuncConfirm}');
		*/
		echo (empty($MorbusHepatitisCure)?'<div style=\'clear:both;\'>Лечение</div>':'{MorbusHepatitisCure}');
		echo (empty($MorbusHepatitisVaccination)?'<div style=\'clear:both;\'>Вакцинация</div>':'{MorbusHepatitisVaccination}');
		echo (empty($MorbusHepatitisQueue)?'<div style=\'clear:both;\'>Очередь</div>':'{MorbusHepatitisQueue}');
		echo (empty($MorbusHepatitisPlan)?'<div style=\'clear:both;\'>План лечения Гепатита C</div>':'{MorbusHepatitisPlan}');

	?>

</div>


