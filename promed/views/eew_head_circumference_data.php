<div
	id="HeadCircumference_{pid}"
	onmouseover="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('HeadCircumference_{pid}_toolbar').style.display='block'
		"
	onmouseout="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('HeadCircumference_{pid}_toolbar').style.display='none'
		">

	<div class="caption">
		<h3>Окружность головы</h3>
		<div class="toolbar" id="HeadCircumference_{pid}_toolbar">
			<a
				id="HeadCircumferenceList_{pid}_addHeadCircumference"
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
				<th>Окружность головы (см)</th>
				<th class="toolbar"></th>
			</tr>
		</thead>

		<tbody id="HeadCircumferenceList_{pid}">

			{items}

		</tbody>

	</table>
</div>
