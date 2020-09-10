<?php
/**
 * @package      MorbusOnko
 */
	$is_allow_edit = (!empty($accessType) && $accessType == 'edit');
?>
		<!--tr id="DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}_toolbar').style.display='none'"-->
		<tr id="DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}" class="list-item<?php if ( $DrugTherapyScheme_IsMes == 1 ) { echo ' highlight'; } ?>"<?php if ( $DrugTherapyScheme_IsMes == 1 ) { echo ' title="Используется в расчёте КСГ"'; } ?>>
			<td>{DrugTherapyScheme_Code}</td>
			<td>{DrugTherapyScheme_Name}</td>
			<td>{DrugTherapyScheme_Days}</td>
			<td>{DrugTherapyScheme_DaysFact}</td>
			<!--td class="toolbar">
				<div id="DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}_toolbar" class="toolbar">
					<a id="DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="DrugTherapyScheme_{MorbusOnko_pid}_{DrugTherapyScheme_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td-->
		</tr>
