<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusTubDiagGeneralForm_{MorbusTub_pid}_{TubDiagGeneralForm_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubDiagGeneralForm_{MorbusTub_pid}_{TubDiagGeneralForm_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubDiagGeneralForm_{MorbusTub_pid}_{TubDiagGeneralForm_id}_toolbar').style.display='none'">
			<td>{TubDiagGeneralForm_setDT}</td>
			<td>{Diag_Name}</td>
			<td class="toolbar">
				<div id="MorbusTubDiagGeneralForm_{MorbusTub_pid}_{TubDiagGeneralForm_id}_toolbar" class="toolbar">
					<a id="MorbusTubDiagGeneralForm_{MorbusTub_pid}_{TubDiagGeneralForm_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusTubDiagGeneralForm_{MorbusTub_pid}_{TubDiagGeneralForm_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
