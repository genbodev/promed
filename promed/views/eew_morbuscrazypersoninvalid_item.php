<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyPersonInvalid_{MorbusCrazy_pid}_{MorbusCrazyPersonInvalid_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonInvalid_{MorbusCrazy_pid}_{MorbusCrazyPersonInvalid_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonInvalid_{MorbusCrazy_pid}_{MorbusCrazyPersonInvalid_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyPersonInvalid_setDT}</td>
			<td>{InvalidGroupType_Name}</td>
			<td>{MorbusCrazyPersonInvalid_reExamDT}</td>
			<td>{CrazyWorkPlaceType_Name}</td>
			
			<td class="toolbar">
				<div id="MorbusCrazyPersonInvalid_{MorbusCrazy_pid}_{MorbusCrazyPersonInvalid_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyPersonInvalid_{MorbusCrazy_pid}_{MorbusCrazyPersonInvalid_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyPersonInvalid_{MorbusCrazy_pid}_{MorbusCrazyPersonInvalid_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
