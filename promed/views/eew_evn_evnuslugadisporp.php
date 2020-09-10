<div id="EvnUslugaDispOrpList_{pid}" class="data-table">
	<div class="caption">
		<h2><span id="EvnUslugaDispOrpList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Обследования
		</span></h2>
	</div>
	<table id="EvnUslugaDispOrpTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Исследован</th>
			<th>Результат</th>
			<th>Код</th>
			<th>Наименование</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="EvnUslugaDispOrpList_{pid}">

		{items}

		</tbody>

	</table>

</div>