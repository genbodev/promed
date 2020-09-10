<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyVizitCheck_{MorbusCrazy_pid}_{MorbusCrazyVizitCheck_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyVizitCheck_{MorbusCrazy_pid}_{MorbusCrazyVizitCheck_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyVizitCheck_{MorbusCrazy_pid}_{MorbusCrazyVizitCheck_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyVizitCheck_setDT}</td>
			<td>{MorbusCrazyVizitCheck_vizitDT}</td>
			<td class="toolbar">
				<div id="MorbusCrazyVizitCheck_{MorbusCrazy_pid}_{MorbusCrazyVizitCheck_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyVizitCheck_{MorbusCrazy_pid}_{MorbusCrazyVizitCheck_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyVizitCheck_{MorbusCrazy_pid}_{MorbusCrazyVizitCheck_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
