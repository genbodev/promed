<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2015
 */
	$is_allow_edit = ($accessType == 'edit');
?>
<div id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Специальное лечение</span></h2>
		<div id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
			<a id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</div>

	<table id="MorbusOnkoSpecTreatTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата начала</th>
			<th>Дата окончания</th>
			<th>Тип</th>
			<th>Сочетание видов лечения</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
