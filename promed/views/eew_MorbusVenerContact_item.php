<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusVenerContact_{MorbusVener_pid}_{MorbusVenerContact_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerContact_{MorbusVener_pid}_{MorbusVenerContact_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerContact_{MorbusVener_pid}_{MorbusVenerContact_id}_toolbar').style.display='none'">
			<td>{Person_Fio}</td>
			<td>{MorbusVenerContact_RelationSick}</td>
			<td>{MorbusVenerContact_CallDT}</td>
			<td>{Diag_Name}</td>
			
			<td class="toolbar">
				<div id="MorbusVenerContact_{MorbusVener_pid}_{MorbusVenerContact_id}_toolbar" class="toolbar">
					<a id="MorbusVenerContact_{MorbusVener_pid}_{MorbusVenerContact_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusVenerContact_{MorbusVener_pid}_{MorbusVenerContact_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
