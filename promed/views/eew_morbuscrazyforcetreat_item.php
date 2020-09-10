<?php 
	$is_allow_edit = (($accessType == 1) && ($accessEvn == 1));
?>
		<tr id="MorbusCrazyForceTreat_{MorbusCrazy_pid}_{MorbusCrazyForceTreat_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyForceTreat_{MorbusCrazy_pid}_{MorbusCrazyForceTreat_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyForceTreat_{MorbusCrazy_pid}_{MorbusCrazyForceTreat_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyForceTreat_begDT} - {MorbusCrazyForceTreat_endDT}</td>
			<td>{CrazyForceTreatType_Name}</td>
			<td class="toolbar">
				<div id="MorbusCrazyForceTreat_{MorbusCrazy_pid}_{MorbusCrazyForceTreat_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyForceTreat_{MorbusCrazy_pid}_{MorbusCrazyForceTreat_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyForceTreat_{MorbusCrazy_pid}_{MorbusCrazyForceTreat_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
