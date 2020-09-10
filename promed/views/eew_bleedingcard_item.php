<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BleedingCard_{BleedingCard_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BleedingCard_{BleedingCard_id}_toolbar').style.display='none'">
	<td>
		<div id="BleedingCard_{BleedingCard_id}">
			<div id="BleedingCard_{BleedingCard_id}_content">{BleedingCard_setDT} Карта наблюдений для оценки кровотечений</div>
		</div>
	</td>
	<td class="toolbar">
		<div id="BleedingCard_{BleedingCard_id}_toolbar" class="toolbar">
			<a id="BleedingCard_{BleedingCard_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
			<a id="BleedingCard_{BleedingCard_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="BleedingCard_{BleedingCard_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
		</div>
	</td>
</tr>