<tr id="BloodData_{PersonBloodGroup_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BloodData_{PersonBloodGroup_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('BloodData_{PersonBloodGroup_id}_toolbar').style.display='none'">
    <td>{BloodGroupType_Name}</td>
    <td>{RhFactorType_Name}</td>
    <td>{PersonBloodGroup_setDate}</td>
    <!--td>{pmUser_Name}</td-->
    <td class="toolbar">
        <div id="BloodData_{PersonBloodGroup_id}_toolbar" class="toolbar">
            <a id="BloodData_{PersonBloodGroup_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="BloodData_{PersonBloodGroup_id}_del" class="button icon icon-delete16" title="Удалить"><span></span></a>
        </div>
    </td>
</tr>
