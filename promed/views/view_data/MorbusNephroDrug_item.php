<?php
	$is_allow_edit = $accessType == 'edit';
?>
<tr id="MorbusNephroDrug_{MorbusNephro_pid}_{MorbusNephroDrug_id}" class="list-item"
    onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDrug_{MorbusNephro_pid}_{MorbusNephroDrug_id}_toolbar').style.display='block'" 
    onmouseout=" if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDrug_{MorbusNephro_pid}_{MorbusNephroDrug_id}_toolbar').style.display='none'">
	<td>{MorbusNephroDrug_begDT} - {MorbusNephroDrug_endDT}</td>
    <td>{DrugComplexMnn_RusName}</td>
    <td>{NephroDrugScheme_Name}</td>
    <td class="toolbar">
		<div id="MorbusNephroDrug_{MorbusNephro_pid}_{MorbusNephroDrug_id}_toolbar" class="toolbar">
			<a id="MorbusNephroDrug_{MorbusNephro_pid}_{MorbusNephroDrug_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</td>
</tr>