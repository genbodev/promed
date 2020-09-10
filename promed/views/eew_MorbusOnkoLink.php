<?php
/**
 * @package      MorbusOnko
 * @author       Пермяков Александр
 * @version      06.2015
 */
?>
<div id="MorbusOnkoLinkList_{MorbusOnko_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLinkList_{MorbusOnko_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLinkList_{MorbusOnko_pid}_{pid}_toolbar').style.display='none'">
	<div class="caption">
		<h2><span id="MorbusOnkoLinkList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Диагностика</span></h2>
		<div id="MorbusOnkoLinkList_{MorbusOnko_pid}_{pid}_toolbar" class="toolbar">
			<a id="MorbusOnkoLinkList_{MorbusOnko_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
		</div>
	</div>

	<table id="MorbusOnkoLinkTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата взятия материала</th>
			<th>Метод подтверждения диагноза </th>
			<th>Тип диагностического показателя</th>
			<th>Диагностический показатель</th>
			<th>Результат диагностики</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
