<?php 
	$is_allow_edit = true;//($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisPlan_{MorbusHepatitis_pid}_{MorbusHepatitisPlan_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisPlan_{MorbusHepatitis_pid}_{MorbusHepatitisPlan_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisPlan_{MorbusHepatitis_pid}_{MorbusHepatitisPlan_id}_toolbar').style.display='none'">
			<td>{MorbusHepatitisPlan_Year}</td>
			<td>{MorbusHepatitisPlan_Month}</td>
			<td>{MedicalCareType_Name}</td>
			<td>{Lpu_Nick}</td>
			<td>{MorbusHepatitisPlan_Treatment}</td>
			<td class="toolbar">
				<div id="MorbusHepatitisPlan_{MorbusHepatitis_pid}_{MorbusHepatitisPlan_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisPlan_{MorbusHepatitis_pid}_{MorbusHepatitisPlan_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisPlan_{MorbusHepatitis_pid}_{MorbusHepatitisPlan_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
