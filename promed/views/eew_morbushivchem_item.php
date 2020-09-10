<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusHIVChem_{MorbusHIV_pid}_{MorbusHIVChem_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVChem_{MorbusHIV_pid}_{MorbusHIVChem_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVChem_{MorbusHIV_pid}_{MorbusHIVChem_id}_toolbar').style.display='none'">
			<td>{Drug_Name}</td>
			<td>{MorbusHIVChem_Dose}</td>
			<td>{MorbusHIVChem_begDT}</td>
			<td>{MorbusHIVChem_endDT}</td>
			<td class="toolbar">
				<div id="MorbusHIVChem_{MorbusHIV_pid}_{MorbusHIVChem_id}_toolbar" class="toolbar">
					<a id="MorbusHIVChem_{MorbusHIV_pid}_{MorbusHIVChem_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHIVChem_{MorbusHIV_pid}_{MorbusHIVChem_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
