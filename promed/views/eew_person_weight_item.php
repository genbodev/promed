<tr id="PersonWeight_{PersonWeight_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonWeight_{PersonWeight_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonWeight_{PersonWeight_id}_toolbar').style.display='none'">
    <td>{PersonWeight_setDate}</td>
    <td>{WeightMeasureType_Name}</td>
    <td>{PersonWeight_Weight}</td>
    <td>{WeightAbnormType_Name}</td>
    <td>{Weight_Index}</td>
    <td class="toolbar">
        <div id="PersonWeight_{PersonWeight_id}_toolbar" class="toolbar">
            <a id="PersonWeight_{PersonWeight_id}_editPersonWeight" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="PersonWeight_{PersonWeight_id}_delPersonWeight" class="button icon icon-delete16" title="Удалить"><span></span></a>
        </div>
    </td>
</tr>
