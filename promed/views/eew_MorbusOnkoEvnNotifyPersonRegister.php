<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      01.2017
 */
$is_allow_edit = (empty($accessType) || 'edit' == $accessType);
?>
<div id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}" class="data-table">
	<div class="caption">
		<h2><span id="MorbusOnkoEvnNotifyList_{MorbusOnko_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Извещения</span></h2>
	</div>

	<table id="MorbusOnkoEvnNotifyTable_{MorbusOnko_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата</th>
			<th>Статус</th>
			<th>Причина отклонения</th>
			<th>Комментарий</th>
			<th class="toolbar"></th>
		</tr>
		</thead>
		<tbody>
		{items}
		</tbody>
	</table>
</div>
