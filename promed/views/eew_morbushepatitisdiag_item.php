<?php 
	$is_allow_edit = ($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisDiag_{MorbusHepatitis_pid}_{MorbusHepatitisDiag_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisDiag_{MorbusHepatitis_pid}_{MorbusHepatitisDiag_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisDiag_{MorbusHepatitis_pid}_{MorbusHepatitisDiag_id}_toolbar').style.display='none'">
			<td>{MorbusHepatitisDiag_setDate}</td>
			<td>{Lpu_Nick}</td>
			<td>{LpuSectionProfile_Name}/{MedPersonal_Name}</td>
			<td>{HepatitisDiagType_Name}</td>
			<td>{MorbusHepatitisDiag_ConfirmDate}</td>
			<td>{HepatitisDiagActiveType_Name}</td>
			<td>{HepatitisFibrosisType_Name}</td>
			<td class="toolbar">
				<div id="MorbusHepatitisDiag_{MorbusHepatitis_pid}_{MorbusHepatitisDiag_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisDiag_{MorbusHepatitis_pid}_{MorbusHepatitisDiag_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisDiag_{MorbusHepatitis_pid}_{MorbusHepatitisDiag_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
