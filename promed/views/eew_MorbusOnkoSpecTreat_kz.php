<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2015
 */
$is_allow_edit = (empty($accessType) || 'edit' == $accessType);
?>
<div id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Специальное лечение</span></h2>
		<div id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
			<a id="MorbusOnkoSpecTreatList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"<?php if ( !$is_allow_edit ) { echo ' style="display: none;"'; } ?>><span></span></a>
		</div>
	</div>

	<table id="MorbusOnkoSpecTreatTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Емдеу түрi (Вид лечения)</th>
			<th>Басталған күн (Дата начала)</th>
			<th>Аяқталған күн (Дата окончания)</th>
			<th>Тип</th>
			<th>Жүргізілген ем туралы мәліметтер (Сведения о проведении лечения)</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
