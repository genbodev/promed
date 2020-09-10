<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyPersonStick_{MorbusCrazy_pid}_{MorbusCrazyPersonStick_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonStick_{MorbusCrazy_pid}_{MorbusCrazyPersonStick_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonStick_{MorbusCrazy_pid}_{MorbusCrazyPersonStick_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyPersonStick_setDT}</td>
			<td>{MorbusCrazyPersonStick_disDT}</td>
			<td>{MorbusCrazyPersonStick_Count}</td>
			<td>{Diag_Name}</td>
			<td class="toolbar">
				<div id="MorbusCrazyPersonStick_{MorbusCrazy_pid}_{MorbusCrazyPersonStick_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyPersonStick_{MorbusCrazy_pid}_{MorbusCrazyPersonStick_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyPersonStick_{MorbusCrazy_pid}_{MorbusCrazyPersonStick_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
