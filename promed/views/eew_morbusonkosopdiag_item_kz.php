<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      02.2017
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoSopDiag_{MorbusOnko_pid}_{MorbusOnkoBaseDiagLink_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSopDiag_{MorbusOnko_pid}_{MorbusOnkoBaseDiagLink_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSopDiag_{MorbusOnko_pid}_{MorbusOnkoBaseDiagLink_id}_toolbar').style.display='none'">
			<td>{SopDiag_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoSopDiag_{MorbusOnko_pid}_{MorbusOnkoBaseDiagLink_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoSopDiag_{MorbusOnko_pid}_{MorbusOnkoBaseDiagLink_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoSopDiag_{MorbusOnko_pid}_{MorbusOnkoBaseDiagLink_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
