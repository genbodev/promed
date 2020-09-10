<tr id="DiagList_{Diag_id}_{spec_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DiagList_{Diag_id}_{spec_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DiagList_{Diag_id}_{spec_id}_toolbar').style.display='none'">
	<td>{Diag_setDate}</td>
    <td>{Diag_Code}</td>
    <td>{Diag_Name}</td>
    <td>{Lpu_Nick}</td>
    <td>{LpuSectionProfile_Name} <img src="/img/icons/staff16.png" class="icon" title="Врач: {MedPersonal_Fio}" /></td>
	<td class="toolbar">
		<div id="DiagList_{Diag_id}_{spec_id}_toolbar" class="toolbar">
			<a id="DiagList_{Diag_id}_{spec_id}_editDiag" class="button icon icon-edit16" title="Редактировать"<?php echo (($spec_id>0)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="DiagList_{Diag_id}_{spec_id}_deleteDiag" class="button icon icon-delete16" title="Удалить"<?php echo (($spec_id>0)?'':' style="display: none;"'); ?>><span></span></a>
		</div>
	</td>
</tr>
