<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazyDrug_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazyDrug_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazyDrug_id}_toolbar').style.display='none'">
			<td>{CrazyDrugType_Name}</td>
			<td>{MorbusCrazyDrug_Name}</td>
			<td>{CrazyDrugReceptType_Name}</td>
			<td class="toolbar">
				<div id="MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazyDrug_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazyDrug_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazyDrug_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>