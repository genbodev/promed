<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusHIVSecDiag_{MorbusHIV_pid}_{MorbusHIVSecDiag_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVSecDiag_{MorbusHIV_pid}_{MorbusHIVSecDiag_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHIVSecDiag_{MorbusHIV_pid}_{MorbusHIVSecDiag_id}_toolbar').style.display='none'">
			<td>{MorbusHIVSecDiag_setDT}</td>
			<td>{Diag_FullName}</td>
			<td class="toolbar">
				<div id="MorbusHIVSecDiag_{MorbusHIV_pid}_{MorbusHIVSecDiag_id}_toolbar" class="toolbar">
					<a id="MorbusHIVSecDiag_{MorbusHIV_pid}_{MorbusHIVSecDiag_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHIVSecDiag_{MorbusHIV_pid}_{MorbusHIVSecDiag_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
