<div id="PersonQuarantine_{pid}" class="data-table component read-only"
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonQuarantine_{pid}_toolbar').style.display='block'"
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonQuarantine_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2>Список контрольных карт по карантину</h2>
		<div id="PersonQuarantine_{pid}_toolbar" class="toolbar">
			<a id="PersonQuarantine_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<a id="PersonQuarantine_{pid}_add" class="button icon icon-add16" title="Добавить наблюдение"><span></span></a>
		</div>
	</div>

	<table>

		<col style="width: 10%" class="first" />
		<col style="width: 20%" />
		<col style="width: 10%" />
		<col style="width: 10%" />
		<col style="width: 10%" />
		<col style="width: 20%" />
		<col style="width: 15%" class="last" />
		<col class = "toolbar"/>

		<thead>
		<tr>
			<th>Дата открытия</th>
			<th>Причина открытия</th>
			<th>Дата контакта/прибытия</th>
			<th>Дней на карантине</th>
			<th>Дата выявления заболевания</th>
			<th>Дата закрытия</th>
			<th>Причина закрытия</th>
		</tr>
		</thead>

		<tbody id="PersonQuarantineList_{pid}">

		{items}

		</tbody>

	</table>

</div>
