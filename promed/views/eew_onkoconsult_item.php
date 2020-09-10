<?php
/**
 * @package      MorbusOnko
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}_toolbar').style.display='none'">
			<td>{OnkoConsult_consDate}</td>
			<td>{OnkoHealType_Name}</td>
			<td>{OnkoConsultResult_Name}</td>
			<td class="toolbar">
				<div id="OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}_toolbar" class="toolbar">
					<a id="OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="OnkoConsult_{MorbusOnko_pid}_{OnkoConsult_id}_print" class="button icon icon-print16" title="Печать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
