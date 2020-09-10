<?php 
	$is_allow_edit = ($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}_toolbar').style.display='none'">
			<td>{MorbusHepatitisCure_begDate}</td>
			<td>{MorbusHepatitisCure_endDate}</td>
			<td>{Drug_Name}</td>
			<td>{HepatitisResultClass_Name}</td>
			<td>{HepatitisSideEffectType_Name}</td>
			<td><span id="MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}_openEffMonitoring" class="link">Мониторинг</span></td>
			<td class="toolbar">
				<div id="MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisCure_{MorbusHepatitis_pid}_{MorbusHepatitisCure_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>