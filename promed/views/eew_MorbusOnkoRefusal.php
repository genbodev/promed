<?php
/**
 * @package      MorbusOnko
 * @author       Быков Станислав
 * @version      03.2019
 */
?>
<div id="MorbusOnkoRefusalList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoRefusalList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoRefusalList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="MorbusOnkoRefusalList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Данные об отказах / противопоказаниях</span></h2>
		<div id="MorbusOnkoRefusalList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
			<a id="MorbusOnkoRefusalList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
		</div>
	</div>

	<table id="MorbusOnkoRefusalTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата регистрации отказа / противопоказания</th>
			<th>Тип лечения</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>