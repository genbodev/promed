<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyPersonSocDangerAct_{MorbusCrazy_pid}_{MorbusCrazyPersonSocDangerAct_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSocDangerAct_{MorbusCrazy_pid}_{MorbusCrazyPersonSocDangerAct_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyPersonSocDangerAct_{MorbusCrazy_pid}_{MorbusCrazyPersonSocDangerAct_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyPersonSocDangerAct_setDT}</td>
			<td>{MorbusCrazyPersonSocDangerAct_Article}</td>
			<td class="toolbar">
				<div id="MorbusCrazyPersonSocDangerAct_{MorbusCrazy_pid}_{MorbusCrazyPersonSocDangerAct_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyPersonSocDangerAct_{MorbusCrazy_pid}_{MorbusCrazyPersonSocDangerAct_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyPersonSocDangerAct_{MorbusCrazy_pid}_{MorbusCrazyPersonSocDangerAct_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
