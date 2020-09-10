<?php 
	$is_allow_edit = true;//($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisQueue_{MorbusHepatitis_pid}_{MorbusHepatitisQueue_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisQueue_{MorbusHepatitis_pid}_{MorbusHepatitisQueue_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisQueue_{MorbusHepatitis_pid}_{MorbusHepatitisQueue_id}_toolbar').style.display='none'">
			<td>{HepatitisQueueType_Name}</td>
			<td>{MorbusHepatitisQueue_Num}</td>
			<td>{MorbusHepatitisQueue_IsCure}</td>
			<td class="toolbar">
				<div id="MorbusHepatitisQueue_{MorbusHepatitis_pid}_{MorbusHepatitisQueue_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisQueue_{MorbusHepatitis_pid}_{MorbusHepatitisQueue_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisQueue_{MorbusHepatitis_pid}_{MorbusHepatitisQueue_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
