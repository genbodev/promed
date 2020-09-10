<tr id="PersonHeight_{PersonHeight_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonHeight_{PersonHeight_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonHeight_{PersonHeight_id}_toolbar').style.display='none'">
    <td>{PersonHeight_setDate}</td>
    <td>{HeightMeasureType_Name}</td>
    <td>{PersonHeight_Height}</td>
    <td>{HeightAbnormType_Name}</td>
    <td class="toolbar">
        <div id="PersonHeight_{PersonHeight_id}_toolbar" class="toolbar">
            <a id="PersonHeight_{PersonHeight_id}_editPersonHeight" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="PersonHeight_{PersonHeight_id}_delPersonHeight" class="button icon icon-delete16" title="Удалить"><span></span></a>
        </div>
    </td>
</tr>
