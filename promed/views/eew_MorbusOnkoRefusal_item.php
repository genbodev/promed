<?php
/**
 * @package      MorbusOnko
 * @author       Быков Станислав
 * @version      03.2019
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoRefusal_{MorbusOnko_pid}_{MorbusOnkoRefusal_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoRefusal_{MorbusOnko_pid}_{MorbusOnkoRefusal_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoRefusal_{MorbusOnko_pid}_{MorbusOnkoRefusal_id}_toolbar').style.display='none'">
			<td>{MorbusOnkoRefusal_setDT}</td>
			<td>{MorbusOnkoRefusalType_id_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoRefusal_{MorbusOnko_pid}_{MorbusOnkoRefusal_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoRefusal_{MorbusOnko_pid}_{MorbusOnkoRefusal_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoRefusal_{MorbusOnko_pid}_{MorbusOnkoRefusal_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>