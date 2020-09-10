<tr id="PersonQuarantine_{PersonQuarantine_id}"
	onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonQuarantine_{PersonQuarantine_id}_toolbar').style.display='block'"
	onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonQuarantine_{PersonQuarantine_id}_toolbar').style.display='none'">
	<td>{PersonQuarantine_begDate}</td>
	<td>{PersonQuarantineOpenReason_Name}</td>
	<td>{arrivalOrContactDate}</td>
	<td>{QuarantineDays}</td>
	<td>{PersonQuarantine_approveDate}</td>
	<td>{PersonQuarantine_endDate}</td>
	<td>{PersonQuarantineCloseReason_Name}</td>
	<td class="toolbar">
		<div id="PersonQuarantine_{PersonQuarantine_id}_toolbar" class="toolbar">
			<a id="PersonQuarantine_{PersonQuarantine_id}_view" class="button icon icon-view16" title="Просмотр" style="display: block"><span></span></a>
			<a id="PersonQuarantine_{PersonQuarantine_id}_edit" class="button icon icon-edit16" title="Изменить"<?php echo empty($PersonQuarantine_endDate)?'':' style="display: none;"'; ?>><span></span></a>
		</div>
	</td>
</tr>
