<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}_toolbar').style.display='none'">
			<td>{MorbusOnkoSpecTreat_specSetDT}</td>
			<td>{MorbusOnkoSpecTreat_specDisDT}</td>
			<td>{TumorPrimaryTreatType_id_Name}</td>
			<td>{OnkoCombiTreatType_id_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
					<a id="MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoSpecTreat_{MorbusOnko_pid}_{MorbusOnkoSpecTreat_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>