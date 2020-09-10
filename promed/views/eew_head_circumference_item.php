<tr
	id="HeadCircumference_{HeadCircumference_id}"
	class="list-item"
	hmtcode = "{HeightMeasureType_Code}"
	onmouseover="
		if (isMouseLeaveOrEnter(event, this) &&
			document.getElementById('HeadCircumference_{HeadCircumference_id}').getAttribute('hmtcode') != '1')
			document.getElementById('HeadCircumference_{HeadCircumference_id}_toolbar').style.display='block'
		"
	onmouseout="
		if (isMouseLeaveOrEnter(event, this))
			document.getElementById('HeadCircumference_{HeadCircumference_id}_toolbar').style.display='none'
		"
	>

	<td>{HeadCircumference_setDate}</td>
	<td>{HeightMeasureType_Name}</td>
	<td>{HeadCircumference_Head}</td>

	<td class="toolbar">
		<div
			id="HeadCircumference_{HeadCircumference_id}_toolbar"
			class="toolbar">

			<a
				id="HeadCircumference_{HeadCircumference_id}_editHeadCircumference"
				class="button icon icon-edit16"
				title="Редактировать"><span></span>
			</a>

			<a
				id="HeadCircumference_{HeadCircumference_id}_delHeadCircumference"
				class="button icon icon-delete16"
				title="Удалить"><span></span>
			</a>
		</div>
	</td>
</tr>
