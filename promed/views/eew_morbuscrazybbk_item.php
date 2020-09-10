<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyBBK_{MorbusCrazy_pid}_{MorbusCrazyBBK_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBBK_{MorbusCrazy_pid}_{MorbusCrazyBBK_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBBK_{MorbusCrazy_pid}_{MorbusCrazyBBK_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyBBK_setDT}</td>
			<td>{CrazyDiag_Name}</td>
			<td>{MorbusCrazyBBK_firstDT}</td>
			<td>{MedicalCareType_Name}</td>
			<td>{CrazyDiag_lName}</td>
			<td>{MorbusCrazyBBK_lidDT}</td>
			<td class="toolbar">
				<div id="MorbusCrazyBBK_{MorbusCrazy_pid}_{MorbusCrazyBBK_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyBBK_{MorbusCrazy_pid}_{MorbusCrazyBBK_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyBBK_{MorbusCrazy_pid}_{MorbusCrazyBBK_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>