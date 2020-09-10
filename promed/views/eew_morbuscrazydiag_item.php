<?php 
	$is_allow_edit = ($accessType == 1);
	$mainRec = (1==$isMain);
?>
		<tr id="MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazyDiag_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazyDiag_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazyDiag_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyDiag_setDT}</td>
			<td>{CrazyDiag_id_Name}</td>
			<td>{Diag_sid_Name}</td>
			<td class="toolbar">
				<div id="MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazyDiag_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazyDiag_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazyDiag_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit&&!$mainRec)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
