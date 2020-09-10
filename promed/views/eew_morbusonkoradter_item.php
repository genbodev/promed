<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}_toolbar').style.display='none'">
			<td>{EvnUsluga_setDate}</td>
			<td>{EvnUsluga_disDate}</td>
			<td>{OnkoUslugaBeamIrradiationType_Name}</td>
			<td>{OnkoUslugaBeamKindType_Name}</td>
			<td>{OnkoUslugaBeamMethodType_Name}</td>
			<td>{FocusType_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
					<a id="MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoRadTer_{MorbusOnko_pid}_{EvnUsluga_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
