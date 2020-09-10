<?php 
	$is_allow_edit = ($accessType == 1);
?>
		<tr id="MorbusVenerTreatSyph_{MorbusVener_pid}_{MorbusVenerTreatSyph_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerTreatSyph_{MorbusVener_pid}_{MorbusVenerTreatSyph_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusVenerTreatSyph_{MorbusVener_pid}_{MorbusVenerTreatSyph_id}_toolbar').style.display='none'">
			<td>{MorbusVenerTreatSyph_NumCourse}</td>
			<td>{MorbusVenerTreatSyph_begDT} - {MorbusVenerTreatSyph_endDT}</td>
			<td>{Drug_Name}</td>
			<td>{MorbusVenerTreatSyph_SumDose}</td>
			<td>{MorbusVenerTreatSyph_RSSBegCourse}</td>
			<td>{MorbusVenerTreatSyph_RSSEndCourse}</td>
			<td class="toolbar">
				<div id="MorbusVenerTreatSyph_{MorbusVener_pid}_{MorbusVenerTreatSyph_id}_toolbar" class="toolbar">
					<a id="MorbusVenerTreatSyph_{MorbusVener_pid}_{MorbusVenerTreatSyph_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusVenerTreatSyph_{MorbusVener_pid}_{MorbusVenerTreatSyph_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
