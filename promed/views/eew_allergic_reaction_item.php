<tr id="AllergHistory_{PersonAllergicReaction_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('AllergHistory_{PersonAllergicReaction_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('AllergHistory_{PersonAllergicReaction_id}_toolbar').style.display='none'">
    <td>{PersonAllergicReaction_setDate}</td>
    <td>{AllergicReactionType_Name}</td>
    <td>{PersonAllergicReaction_Kind}</td>
    <td>{AllergicReactionLevel_Name}</td>
    <td class="toolbar">
        <div id="AllergHistory_{PersonAllergicReaction_id}_toolbar" class="toolbar">
            <a id="AllergHistory_{PersonAllergicReaction_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="AllergHistory_{PersonAllergicReaction_id}_del" class="button icon icon-delete16" title="Удалить"><span></span></a>
        </div>
    </td>
</tr>
