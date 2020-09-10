<div id="EvnDiagAndRecomendationList_{pid}" class="data-table">
	<div class="caption">
		<h2><span id="EvnDiagAndRecomendationList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">
			Диагнозы и рекомендации
		</span></h2>
	</div>
	<table id="EvnDiagAndRecomendationTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="first" />
		<col class="last" />
		<col class="toolbar"/>
		<thead>
		<tr>
			<th>Специальность</th>
			<th>Диагноз</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="EvnDiagAndRecomendationList_{pid}">

		{items}

		</tbody>

	</table>

</div>