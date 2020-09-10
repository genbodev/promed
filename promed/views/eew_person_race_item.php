<tr
	id="PersonRace_{PersonRace_id}"
	class="list-item"
	name="PersonRace_{Person_id}"
	onmouseover="
		if (isMouseLeaveOrEnter(event, this) &&
			document.getElementById('PersonRace_{PersonRace_id}'))
			document.getElementById('PersonRace_{PersonRace_id}_toolbar').style.display='block'
		"
	onmouseout="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('PersonRace_{PersonRace_id}_toolbar').style.display='none'
		">

	<td>{PersonRace_setDT}</td>
	<td>{RaceType_Name}</td>
	<td class="toolbar">
		<div
			id="PersonRace_{PersonRace_id}_toolbar"
			class="toolbar">

			<a
				id="PersonRace_{PersonRace_id}_editPersonRace"
				class="button icon icon-edit16"
				title="Редактировать"><span></span>
			</a>

			<a
					id="PersonRace_{PersonRace_id}_delPersonRace"
					class="button icon icon-delete16"
					title="Удалить"><span></span>
			</a>
		</div>
	</td>
</tr>
