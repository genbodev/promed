<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusCrazyBaseDrugStart_{MorbusCrazy_pid}_{MorbusCrazyBaseDrugStart_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBaseDrugStart_{MorbusCrazy_pid}_{MorbusCrazyBaseDrugStart_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBaseDrugStart_{MorbusCrazy_pid}_{MorbusCrazyBaseDrugStart_id}_toolbar').style.display='none'">
			<td>{MorbusCrazyBaseDrugStart_Name}</td>
			<td>{CrazyDrugReceptType_Name}</td>
			<td>{MorbusCrazyBaseDrugStart_Age}</td>
			<td class="toolbar">
				<div id="MorbusCrazyBaseDrugStart_{MorbusCrazy_pid}_{MorbusCrazyBaseDrugStart_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyBaseDrugStart_{MorbusCrazy_pid}_{MorbusCrazyBaseDrugStart_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyBaseDrugStart_{MorbusCrazy_pid}_{MorbusCrazyBaseDrugStart_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
