<?php 
	$is_allow_edit = ($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisVaccination_{MorbusHepatitis_pid}_{MorbusHepatitisVaccination_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisVaccination_{MorbusHepatitis_pid}_{MorbusHepatitisVaccination_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisVaccination_{MorbusHepatitis_pid}_{MorbusHepatitisVaccination_id}_toolbar').style.display='none'">
			<td>{MorbusHepatitisVaccination_setDate}</td>
			<td>{Drug_Name}</td>
			<td class="toolbar">
				<div id="MorbusHepatitisVaccination_{MorbusHepatitis_pid}_{MorbusHepatitisVaccination_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisVaccination_{MorbusHepatitis_pid}_{MorbusHepatitisVaccination_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisVaccination_{MorbusHepatitis_pid}_{MorbusHepatitisVaccination_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
