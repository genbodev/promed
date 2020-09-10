<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusTubMDRPrescr_{MorbusTub_pid}_{MorbusTubPrescr_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRPrescr_{MorbusTub_pid}_{MorbusTubPrescr_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRPrescr_{MorbusTub_pid}_{MorbusTubPrescr_id}_toolbar').style.display='none'">
			<td>{MorbusTubPrescr_setDT}<?php echo (isset($MorbusTubPrescr_endDate) ? ' / {MorbusTubPrescr_endDate}':'' ); ?></td>
			<td>{TubDrug_Name}</td>
            <td>{MorbusTubPrescr_SetDay}</td>
            <td>{MorbusTubPrescr_MissDay}</td>
			<td class="toolbar">
				<div id="MorbusTubMDRPrescr_{MorbusTub_pid}_{MorbusTubPrescr_id}_toolbar" class="toolbar">
					<a id="MorbusTubMDRPrescr_{MorbusTub_pid}_{MorbusTubPrescr_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusTubMDRPrescr_{MorbusTub_pid}_{MorbusTubPrescr_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
