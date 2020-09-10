<div id="EvnVizitDispList_{pid}" class="data-table">
	<div class="caption">
		<h2><span id="EvnVizitDispList_{pid}_toggleDisplayDiagList" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Выявленные заболевания
		</span></h2>
	</div>
	<table id="EvnVizitDispTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col style="width: 15%" />
		<col style="width: 10%" />
		<col style="width: 10%" />
		<col style="width: 10%" class="last" />

		<thead>
			<tr>
				<th>Диагноз</th>
				<th>Код МКБ-10</th>
				<th>Диагноз установлен впервые</th>
				<th>Д-наблюдение</th>
				<th>ВМП рекомендована</th>
			</tr>
		</thead>

		<tbody id="EvnVizitDispList_{pid}">

		{items}

		</tbody>

	</table>

</div>