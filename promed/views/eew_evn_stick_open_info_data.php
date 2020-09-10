<div id="EvnStickOpenInfo_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnStickOpenInfo_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnStickOpenInfo_{pid}_toolbar').style.display='none'">

	<div class="caption">
		<h2>Список открытых ЛВН</h2>
		<div id="EvnStickOpenInfo_{pid}_toolbar" class="toolbar">
			<a id="EvnStickOpenInfo_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<span id="EvnStickOpenInfo_{pid}_containerWorkReleaseCalculation" style="display: none;">
				<a id="EvnStickOpenInfo_{pid}_openWorkReleaseCalculation" class="button" title="Дней нетрудоспособности в году" style="padding: 2px;"><span>Дней нетрудоспособности в году</span></a>
			</span>
		</div>
	</div>

	<table>

		<col style="width: 10%" class="first" />
		<col style="width: 15%" />
		<col style="width: 15%" />
		<col />
		<col class="last" />
		<col class="toolbar" />

		<thead>
		<tr>
			<th>Серия</th>
			<th>Номер</th>
			<th>Дата выдачи</th>
			<th>Тип занятости</th>
			<th>Порядок выдачи</th>
			<th class="toolbar">
		</tr>
		</thead>

		<tbody id="EvnStickOpenInfoList_{pid}">

		{items}

		</tbody>

	</table>

</div>