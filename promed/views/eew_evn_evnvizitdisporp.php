<div id="EvnVizitDispOrpList_{pid}" class="data-table">
	<div class="caption">
		<h2><span id="EvnVizitDispOrpList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Осмотр врача-специалиста
		</span></h2>
	</div>
	<table id="EvnVizitDispOrpTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Дата посещения</th>
			<th>Специалность</th>
			<th>Диагноз</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="EvnVizitDispOrpList_{pid}">

		{items}

		</tbody>

	</table>

</div>