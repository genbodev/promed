<?php 
	$is_allow_edit = (($accessType == 1) && ($accessEvn == 1));
?>
		<tr id="MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazyBasePS_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazyBasePS_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazyBasePS_id}_toolbar').style.display='none'">
			<td>{Lpu_Nick}</td>
			<td>{CrazyPurposeHospType_Name}</td>
			<td>{MorbusCrazyBasePS_setDT} - {MorbusCrazyBasePS_disDT}</td>
			<td>{CrazyDiag_id_Name}</td>
			<td>{Diag_id_Name}</td>
			<td class="toolbar">
				<div id="MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazyBasePS_id}_toolbar" class="toolbar">
					<a id="MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazyBasePS_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazyBasePS_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
