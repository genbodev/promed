<div
	id="PersonRace_{pid}"
	onmouseover="
		if (isMouseLeaveOrEnter(event, this) &&
			!document.getElementsByName('PersonRace_{pid}')[0])
			document.getElementById('PersonRace_{pid}_toolbar').style.display='block'
		"
	onmouseout="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('PersonRace_{pid}_toolbar').style.display='none'
		">

	<div class="caption">
		<h3>Раса</h3>
		<div class="toolbar" id="PersonRace_{pid}_toolbar">
			<a
				id="PersonRaceList_{pid}_addPersonRace"
				class="button icon icon-add16"
				title="Добавить"><span></span>
			</a>
		</div>
	</div>

	<table>

		<col style="width: 10%" class="first" />
		<col style="width: 20%" />
		<col class="toolbar"/>

		<thead>
		<tr>
			<th>Дата внесения</th>
			<th>Раса</th>
			<th class="toolbar"></th>
		</tr>
		</thead>

		<tbody id="PersonRaceList_{pid}">

		{items}

		</tbody>

	</table>
</div>
