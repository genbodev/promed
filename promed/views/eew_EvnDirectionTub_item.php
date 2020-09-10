<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="EvnDirectionTub_{MorbusTub_pid}_{EvnDirectionTub_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionTub_{MorbusTub_pid}_{EvnDirectionTub_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionTub_{MorbusTub_pid}_{EvnDirectionTub_id}_toolbar').style.display='none'">
			<td>{EvnDirectionTub_setDT}</td>
			<td>{TubDiagnosticMaterialType_Name}</td>
			<td>{EvnDirectionTub_ResDT}</td>
			<td class="toolbar">
				<div id="EvnDirectionTub_{MorbusTub_pid}_{EvnDirectionTub_id}_toolbar" class="toolbar">
					<a id="EvnDirectionTub_{MorbusTub_pid}_{EvnDirectionTub_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="EvnDirectionTub_{MorbusTub_pid}_{EvnDirectionTub_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
