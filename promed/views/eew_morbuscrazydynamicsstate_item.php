<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyDynamicsState_{MorbusCrazy_pid}_{MorbusCrazyDynamicsState_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDynamicsState_{MorbusCrazy_pid}_{MorbusCrazyDynamicsState_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDynamicsState_{MorbusCrazy_pid}_{MorbusCrazyDynamicsState_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyDynamicsState_begDT}</td>
			<td>{MorbusCrazyDynamicsState_endDT}</td>
			<td>{MorbusCrazyDynamicsState_Count}</td>
			<td class="toolbar">
				<div id="MorbusCrazyDynamicsState_{MorbusCrazy_pid}_{MorbusCrazyDynamicsState_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyDynamicsState_{MorbusCrazy_pid}_{MorbusCrazyDynamicsState_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyDynamicsState_{MorbusCrazy_pid}_{MorbusCrazyDynamicsState_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
