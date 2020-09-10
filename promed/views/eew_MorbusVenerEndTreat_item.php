<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusVenerEndTreat_{MorbusVener_pid}_{MorbusVenerEndTreat_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerEndTreat_{MorbusVener_pid}_{MorbusVenerEndTreat_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerEndTreat_{MorbusVener_pid}_{MorbusVenerEndTreat_id}_toolbar').style.display='none'">
			<td>{MorbusVenerEndTreat_setDT}</td>
			<td>{MorbusVenerEndTreat_CallDT}</td>
			<td>{MorbusVenerEndTreat_PresDT}</td>
			<td class="toolbar">
				<div id="MorbusVenerEndTreat_{MorbusVener_pid}_{MorbusVenerEndTreat_id}_toolbar" class="toolbar">
					<a id="MorbusVenerEndTreat_{MorbusVener_pid}_{MorbusVenerEndTreat_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusVenerEndTreat_{MorbusVener_pid}_{MorbusVenerEndTreat_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
