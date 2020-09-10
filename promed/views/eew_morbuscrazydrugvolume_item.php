<?php 
	$is_allow_edit = (($accessType == 1) && ($accessEvn == 1));
?>
		<tr id="MorbusCrazyDrugVolume_{MorbusCrazy_pid}_{MorbusCrazyDrugVolume_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDrugVolume_{MorbusCrazy_pid}_{MorbusCrazyDrugVolume_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyDrugVolume_{MorbusCrazy_pid}_{MorbusCrazyDrugVolume_id}_toolbar').style.display='none'">
			<td>{Lpu_Nick}</td>
			<td>{MorbusCrazyDrugVolume_setDT}</td>
			<td>{CrazyDrugVolumeType_Name}</td>
			<td class="toolbar">
				<div id="MorbusCrazyDrugVolume_{MorbusCrazy_pid}_{MorbusCrazyDrugVolume_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyDrugVolume_{MorbusCrazy_pid}_{MorbusCrazyDrugVolume_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyDrugVolume_{MorbusCrazy_pid}_{MorbusCrazyDrugVolume_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
