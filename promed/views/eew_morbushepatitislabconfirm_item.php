<?php 
	$empty_str = '<span style="color: #666;">Не указано</span>';
	$is_allow_edit = ($AccessType == 'edit');
?>
		<tr id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_toolbar').style.display='none'">
			<td><span class="link" id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_openUsluga">{MorbusHepatitisLabConfirm_setDate}</span></td>
			<td>{HepatitisLabConfirmType_Name}</td>
			<td><?php if($is_allow_edit) { ?><span id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_inputLabConfirmResult" style='color:#000;' class="link"><?php } echo empty($MorbusHepatitisLabConfirm_Result)?$empty_str:'{MorbusHepatitisLabConfirm_Result}'; if($is_allow_edit) { ?></span><?php } ?><div id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_inputareaLabConfirmResult" class="input-area" style="float:left; margin-left:5px; display: none"></div></td>
			<td class="toolbar">
				<div id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_toolbar" class="toolbar">
					<a id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusHepatitisLabConfirm_{MorbusHepatitis_pid}_{MorbusHepatitisLabConfirm_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
