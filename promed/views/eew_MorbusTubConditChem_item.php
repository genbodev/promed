<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusTubConditChem_{MorbusTub_pid}_{MorbusTubConditChem_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubConditChem_{MorbusTub_pid}_{MorbusTubConditChem_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubConditChem_{MorbusTub_pid}_{MorbusTubConditChem_id}_toolbar').style.display='none'">
			<td>{TubStandartConditChemType_Name}</td>
			<!--<td>{TubTreatmentChemType_Name}</td>-->
			<td>{TubStageChemType_Name}</td>
			<td>{TubVenueType_Name}</td>
			<td><?php echo (empty($MorbusTubConditChem_EndDate)?'c {MorbusTubConditChem_BegDate}':'{MorbusTubConditChem_BegDate}&nbsp;-&nbsp;{MorbusTubConditChem_EndDate}'); ?></td>
			<td class="toolbar">
				<div id="MorbusTubConditChem_{MorbusTub_pid}_{MorbusTubConditChem_id}_toolbar" class="toolbar">
					<a id="MorbusTubConditChem_{MorbusTub_pid}_{MorbusTubConditChem_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusTubConditChem_{MorbusTub_pid}_{MorbusTubConditChem_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
