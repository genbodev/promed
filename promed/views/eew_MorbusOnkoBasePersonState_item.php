<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoBasePersonState_{MorbusOnko_pid}_{MorbusOnkoBasePersonState_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoBasePersonState_{MorbusOnko_pid}_{MorbusOnkoBasePersonState_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoBasePersonState_{MorbusOnko_pid}_{MorbusOnkoBasePersonState_id}_toolbar').style.display='none'">
			<td>{MorbusOnkoBasePersonState_setDT}</td>
			<td>{OnkoPersonStateType_id_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoBasePersonState_{MorbusOnko_pid}_{MorbusOnkoBasePersonState_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoBasePersonState_{MorbusOnko_pid}_{MorbusOnkoBasePersonState_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoBasePersonState_{MorbusOnko_pid}_{MorbusOnkoBasePersonState_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
