<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoLink_{MorbusOnko_pid}_{MorbusOnkoLink_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLink_{MorbusOnko_pid}_{MorbusOnkoLink_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLink_{MorbusOnko_pid}_{MorbusOnkoLink_id}_toolbar').style.display='none'">
			<td>{MorbusOnkoLink_takeDT}</td>
			<td>{OnkoDiagConfType_id_Name}</td>
			<td>{DiagAttribType_id_Name}</td>
			<td>{DiagAttribDict_id_Name}</td>
			<td>{DiagResult_id_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoLink_{MorbusOnko_pid}_{MorbusOnkoLink_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoLink_{MorbusOnko_pid}_{MorbusOnkoLink_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoLink_{MorbusOnko_pid}_{MorbusOnkoLink_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>