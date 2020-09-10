<div
	id="ChestCircumference_{pid}"
	onmouseover="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('ChestCircumference_{pid}_toolbar').style.display='block'
		"
	onmouseout="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('ChestCircumference_{pid}_toolbar').style.display='none'
		">

	<div class="caption">
		<h3>Окружность груди</h3>
		<div class="toolbar" id="ChestCircumference_{pid}_toolbar">
			<a
				id="ChestCircumferenceList_{pid}_addChestCircumference"
				class="button icon icon-add16"
				title="Добавить"><span></span>
			</a>
		</div>
	</div>

	<table>

		<col style="width: 10%" class="first" />
		<col style="width: 20%" />
		<col class="last" />
		<col class="toolbar"/>

		<thead>
			<tr>
				<th>Дата измерения</th>
				<th>Вид замера</th>
				<th>Окружность груди (см)</th>
				<th class="toolbar"></th>
			</tr>
		</thead>

		<tbody id="ChestCircumferenceList_{pid}">

			{items}

		</tbody>

	</table>
</div>
