<tr id="PersonDispInfo_{PersonDisp_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonDispInfo_{PersonDisp_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonDispInfo_{PersonDisp_id}_toolbar').style.display='none'">
    <td>{PersonDisp_begDate}</td>
    <td>{Diag_Code}</td>
    <td>{Diag_Name}</td>
    <td>{PersonDisp_endDate}</td>
    <td>{DispOutType_Name}</td>
    <td>{LpuSectionProfile_Name} <img src="/img/icons/staff16.png" class="icon" title="Ответственный врач: {MedPersonal_Fio}" /></td>
	<td>
	<?php if ($signAccess == 'edit') { ?>
		<div class="emd-here" data-objectname="PersonDisp" data-objectid="{PersonDisp_id}" data-issigned="{PersonDisp_IsSignedEP}"></div>
	<?php } ?>
	</td>
    <td class="toolbar">
        <div id="PersonDispInfo_{PersonDisp_id}_toolbar" class="toolbar">
            <a id="PersonDispInfo_{PersonDisp_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="PersonDispInfo_{PersonDisp_id}_printitem" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </td>
</tr>
