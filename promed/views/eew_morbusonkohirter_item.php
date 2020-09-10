<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2013
 */
	$is_allow_edit = ($accessType == 'edit');
?>
		<tr id="MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}_toolbar').style.display='none'">
			<td>{accessType}</td>
			<td>{EvnUsluga_disDate}</td>
			<td>{Lpu_Name}</td>
			<td>{MedPersonal_Name}</td>
			<td>{Usluga_Name}</td>
			<td class="toolbar">
				<div id="MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}_toolbar" class="toolbar">
					<a id="MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
					<a id="MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
					<a id="MorbusOnkoHirTer_{MorbusOnko_pid}_{EvnUsluga_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
				</div>
			</td>
		</tr>
