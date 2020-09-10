<?php
$is_allow_edit = ($accessType == 'edit');
?>
<tr id="MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}_toolbar').style.display='none'">
	<td>{MorbusOnkoDrug_begDate}</td>
	<td>{MorbusOnkoDrug_endDate}</td>
	<td>{Prep_Name}</td>
	<td>{OnkoDrug_Name}</td>
	<td class="toolbar">
		<div id="MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}_toolbar" class="toolbar">
			<a id="MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}_view" class="button icon icon-view16" title="Просмотр"<?php echo ((!$is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="MorbusOnkoDrug_{MorbusOnko_pid}_{MorbusOnkoDrug_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</td>
</tr>