<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_toolbar').style.display='none'">
			<td><span class="link" id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_openUsluga">{MorbusHepatitisFuncConfirm_setDate}</span></td>
			<td>{HepatitisFuncConfirmType_Name}</td>
			<td><?php if($is_allow_edit) { ?><span id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_inputFuncConfirmResult" style='color:#000;' class="link"><?php } echo empty($MorbusHepatitisFuncConfirm_Result)?$empty_str:'{MorbusHepatitisFuncConfirm_Result}'; if($is_allow_edit) { ?></span><?php } ?><div id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_inputareaFuncConfirmResult" class="input-area" style="float:left; margin-left:5px; display: none"></div></td>
			<td class="toolbar">
				<div id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisFuncConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisFuncConfirm_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
