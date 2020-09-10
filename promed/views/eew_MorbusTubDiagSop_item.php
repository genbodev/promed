<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusTubDiagSop_{MorbusTub_pid}_{MorbusTubDiagSop_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubDiagSop_{MorbusTub_pid}_{MorbusTubDiagSop_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubDiagSop_{MorbusTub_pid}_{MorbusTubDiagSop_id}_toolbar').style.display='none'">
			<td>{MorbusTubDiagSop_setDT}</td>
			<td>{TubDiagSop_Name}</td>
			<td class="toolbar">
				<div id="MorbusTubDiagSop_{MorbusTub_pid}_{MorbusTubDiagSop_id}_toolbar" class="toolbar">
					<a id="MorbusTubDiagSop_{MorbusTub_pid}_{MorbusTubDiagSop_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusTubDiagSop_{MorbusTub_pid}_{MorbusTubDiagSop_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
