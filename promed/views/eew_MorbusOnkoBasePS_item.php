<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoBasePS_{MorbusOnko_pid}_{MorbusOnkoBasePS_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoBasePS_{MorbusOnko_pid}_{MorbusOnkoBasePS_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoBasePS_{MorbusOnko_pid}_{MorbusOnkoBasePS_id}_toolbar').style.display='none'">
			<td>{MorbusOnkoBasePS_setDT}</td>
			<td>{MorbusOnkoBasePS_disDT}</td>
			<td>{OnkoPurposeHospType_id_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoBasePS_{MorbusOnko_pid}_{MorbusOnkoBasePS_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoBasePS_{MorbusOnko_pid}_{MorbusOnkoBasePS_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoBasePS_{MorbusOnko_pid}_{MorbusOnkoBasePS_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
