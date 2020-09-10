<tr id="NeedConsult_{NeedConsult_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NeedConsult_{NeedConsult_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NeedConsult_{NeedConsult_id}_toolbar').style.display='none'">
	<td>{Post_Name}</td>
	<td>{ConsultationType_Name}</td>
	<td class="toolbar">
		<div id="NeedConsult_{NeedConsult_id}_toolbar" class="toolbar">
			<a id="NeedConsult_{NeedConsult_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
			<a id="NeedConsult_{NeedConsult_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="NeedConsult_{NeedConsult_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
		</div>
	</td>
</tr>