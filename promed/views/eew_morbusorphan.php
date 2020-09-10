<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = ('edit' == $accessType);
?>
<div id="MorbusOrphan_{From_id}_{PersonRegister_id}" class="specifics">
	<div class="data-row-container"><div class="data-row">Диагноз заболевания: <?php
		echo ($is_allow_edit && isSuperadmin()) ? '<span id="MorbusOrphan_{From_id}_{PersonRegister_id}_inputDiag" class="value link" dataid="{Diag_id}">' : '<span class="value">';
		echo empty($Diag_Name) ? $empty_str : '{Diag_Name}';
		echo '</span></div>';
		echo ($is_allow_edit && isSuperadmin()) ? '<div id="MorbusOrphan_{From_id}_{PersonRegister_id}_inputareaDiag" class="input-area"></div>' : '';
	?></div>
	<div class="data-row-container"><div class="data-row">ЛПУ, в которой пациенту впервые установлен диагноз орфанного заболевания: <?php
		echo ($is_allow_edit) ? '<span id="MorbusOrphan_{From_id}_{PersonRegister_id}_inputLpuO" class="value link" dataid="{Lpu_oid}">' : '<span class="value">';
		echo empty($LpuO_Name) ? $empty_str : '{LpuO_Name}';
		echo '</span></div>';
		echo ($is_allow_edit) ? '<div id="MorbusOrphan_{From_id}_{PersonRegister_id}_inputareaLpuO" class="input-area"></div>' : '';
	?></div>
	<div class="data-row-container"><div class="data-row">ЛПУ создания Извещения: <span class="value"><?php echo empty($LpuN_Name) ? $empty_str : '{LpuN_Name}'; ?></span></div></div>
	{PersonPrivilege}
	{PersonPrivilegeFed}
	{DrugOrphan}
	<div class="data-row-container"><div class="data-row">Дата включения в федеральный регистр: <span class="value"><?php echo empty($PersonRegister_setDate) ? $empty_str : '{PersonRegister_setDate}'; ?></span></div></div>
	<div class="data-row-container"><div class="data-row">Дата исключения из федерального регистра: <span class="value"><?php echo empty($PersonRegister_disDate) ? $empty_str : '{PersonRegister_disDate}'; ?></span></div></div>
	<div class="data-row-container"><div class="data-row">Номер регистровой записи: <span class="value"><?php echo empty($PersonRegister_Code) ? $empty_str : '{PersonRegister_Code}'; ?></span></div></div>
	{PersonRegisterExport}
</div>



