<tr
	id="ChestCircumference_{ChestCircumference_id}"
	class="list-item"
	hmtcode = "{HeightMeasureType_Code}"
	onmouseover="
		if (isMouseLeaveOrEnter(event, this) &&
			document.getElementById('ChestCircumference_{ChestCircumference_id}').getAttribute('hmtcode') != '1')
			document.getElementById('ChestCircumference_{ChestCircumference_id}_toolbar').style.display='block'
		"
	onmouseout="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('ChestCircumference_{ChestCircumference_id}_toolbar').style.display='none'
		">

	<td>{ChestCircumference_setDate}</td>
	<td>{HeightMeasureType_Name}</td>
	<td>{ChestCircumference_Chest}</td>
	<td class="toolbar">
		<div
			id="ChestCircumference_{ChestCircumference_id}_toolbar"
			class="toolbar">

			<a
				id="ChestCircumference_{ChestCircumference_id}_editChestCircumference"
				class="button icon icon-edit16"
				title="Редактировать"><span></span>
			</a>

			<a
				id="ChestCircumference_{ChestCircumference_id}_delChestCircumference"
				class="button icon icon-delete16"
				title="Удалить"><span></span>
			</a>
		</div>
	</td>
</tr>
