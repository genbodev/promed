<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnMediaData_{EvnMediaData_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnMediaData_{EvnMediaData_id}_toolbar').style.display='none'">
    <td>
        <div id="EvnMediaData_{EvnMediaData_id}">
            <!--div id="EvnMediaData_{EvnMediaData_id}_content"> <a href="{EvnMediaData__Dir}{EvnMediaData_FilePath}" target="_blank" title="{EvnMediaData_Comment}">{EvnMediaData_FileName}</a> &raquo; <a href="{EvnMediaData_Link}">Быстрый просмотр</a></div-->
			<div id="EvnMediaData_{EvnMediaData_id}_content"> <a href="{EvnMediaData__Dir}{EvnMediaData_FilePath}" target="_blank" title="{EvnMediaData_Comment}">{EvnMediaData_FileName}</a> &raquo; <a target="_blank"  href="{EvnMediaData_Link}">Быстрый просмотр</a></div>
			
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnMediaData_{EvnMediaData_id}_toolbar" class="toolbar">
            <a id="EvnMediaData_{EvnMediaData_id}_delete" class="button icon icon-delete16" title="Удалить файл"><span></span></a>
        </div>
    </td>
</tr>
