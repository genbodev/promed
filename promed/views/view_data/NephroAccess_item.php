<?php
	$is_allow_edit = $accessType == 'edit';
?>
<tr id="NephroAccess_{MorbusNephro_pid}_{NephroAccess_id}" class="list-item" 
	onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroAccess_{MorbusNephro_pid}_{NephroAccess_id}_toolbar').style.display='block'" 
	onmouseout=" if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroAccess_{MorbusNephro_pid}_{NephroAccess_id}_toolbar').style.display='none'">
	<td>{NephroAccess_setDate}</td>
	<td>{NephroAccessType_Name}</td>
	<td class="toolbar">
		<div id="NephroAccess_{MorbusNephro_pid}_{NephroAccess_id}_toolbar" class="toolbar">
			<a id="NephroAccess_{MorbusNephro_pid}_{NephroAccess_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="NephroAccess_{MorbusNephro_pid}_{NephroAccess_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</td>
</tr>