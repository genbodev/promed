<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MigrantContact_{MigrantContact_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MigrantContact_{MigrantContact_id}_toolbar').style.display='none'">
    <td>
        <div id="MigrantContact_{MigrantContact_id}">
			<div id="MigrantContact_{MigrantContact_id}_content"> {MigrantContact_Name} </div>
        </div>
    </td>
    <td class="toolbar">
        <div id="MigrantContact_{MigrantContact_id}_toolbar" class="toolbar">
            <a id="MigrantContact_{MigrantContact_id}_del" class="button icon icon-delete16" title="Удалить контактное лицо"><span></span></a>
        </div>
    </td>
</tr>
