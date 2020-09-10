<?php
	$is_allow_edit = $accessType == 'edit';
?>
<tr id="NephroCommission_{MorbusNephro_pid}_{NephroCommission_id}" class="list-item" 
	onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroCommission_{MorbusNephro_pid}_{NephroCommission_id}_toolbar').style.display='block'" 
	onmouseout=" if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroCommission_{MorbusNephro_pid}_{NephroCommission_id}_toolbar').style.display='none'">
	<td>{NephroCommission_date}</td>
	<td>{NephroCommission_protocolNumber}</td>
	<td class="toolbar">
		<div id="NephroCommission_{MorbusNephro_pid}_{NephroCommission_id}_toolbar" class="toolbar">
			<a id="NephroCommission_{MorbusNephro_pid}_{NephroCommission_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="NephroCommission_{MorbusNephro_pid}_{NephroCommission_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</td>
</tr>