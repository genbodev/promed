<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazyDynamicsObserv_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazyDynamicsObserv_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazyDynamicsObserv_id}_toolbar').style.display='none'">
			<td>{Lpu_Nick}</td>
			<td>{CrazyAmbulMonitoringType_Name}</td>
			<td>{MorbusCrazyDynamicsObserv_setDT}</td>
			<td class="toolbar">
				<div id="MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazyDynamicsObserv_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazyDynamicsObserv_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazyDynamicsObserv_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>