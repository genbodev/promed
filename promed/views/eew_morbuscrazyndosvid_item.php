<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyNdOsvid_{MorbusCrazy_pid}_{MorbusCrazyNdOsvid_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyNdOsvid_{MorbusCrazy_pid}_{MorbusCrazyNdOsvid_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyNdOsvid_{MorbusCrazy_pid}_{MorbusCrazyNdOsvid_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyNdOsvid_setDT}</td>
			<td>{Lpu_Nick}</td>
			<td class="toolbar">
				<div id="MorbusCrazyNdOsvid_{MorbusCrazy_pid}_{MorbusCrazyNdOsvid_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyNdOsvid_{MorbusCrazy_pid}_{MorbusCrazyNdOsvid_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyNdOsvid_{MorbusCrazy_pid}_{MorbusCrazyNdOsvid_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
