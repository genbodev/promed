<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusHIVVac_{MorbusHIV_pid}_{MorbusHIVVac_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVVac_{MorbusHIV_pid}_{MorbusHIVVac_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVVac_{MorbusHIV_pid}_{MorbusHIVVac_id}_toolbar').style.display='none'">
			<td>{MorbusHIVVac_setDT}</td>
			<td>{Drug_Name}</td>
			<td class="toolbar">
				<div id="MorbusHIVVac_{MorbusHIV_pid}_{MorbusHIVVac_id}_toolbar" class="toolbar">
					<a id="MorbusHIVVac_{MorbusHIV_pid}_{MorbusHIVVac_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHIVVac_{MorbusHIV_pid}_{MorbusHIVVac_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
