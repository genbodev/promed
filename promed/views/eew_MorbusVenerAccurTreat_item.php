<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusVenerAccurTreat_{MorbusVener_pid}_{MorbusVenerAccurTreat_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerAccurTreat_{MorbusVener_pid}_{MorbusVenerAccurTreat_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerAccurTreat_{MorbusVener_pid}_{MorbusVenerAccurTreat_id}_toolbar').style.display='none'">
			<td>{MorbusVenerAccurTreat_AbandDT}</td>
			<td>{MorbusVenerAccurTreat_CallDT}</td>
			<td>{MorbusVenerAccurTreat_PresDT}</td>
			<td class="toolbar">
				<div id="MorbusVenerAccurTreat_{MorbusVener_pid}_{MorbusVenerAccurTreat_id}_toolbar" class="toolbar">
					<a id="MorbusVenerAccurTreat_{MorbusVener_pid}_{MorbusVenerAccurTreat_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusVenerAccurTreat_{MorbusVener_pid}_{MorbusVenerAccurTreat_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
