<tr id="ExpertHistory_{PersonPrivilege_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('ExpertHistory_{PersonPrivilege_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('ExpertHistory_{PersonPrivilege_id}_toolbar').style.display='none'">
    <td>{PrivilegeType_Name}<?php if(!empty($SubCategoryPrivType_Name)){ ?> / {SubCategoryPrivType_Name}<?php } ?><?php if(!empty($Diag_Name)){ ?><br/>Диагноз: {Diag_Name}<?php } ?><?php if(!empty($DocumentPrivilege_Data)){ ?><br/>{DocumentPrivilege_Data}<?php } ?></td>
    <td>{PersonPrivilege_begDate} <!--span id="ExpertHistoryList_{pid}_closeExpertHistory" class="section-button" title="Закрыть">[закрыть]</span--></td>
    <td>{PersonPrivilege_endDate}<?php if(!empty($PrivilegeCloseType_Name)){ ?><br/>{PrivilegeCloseType_Name}<?php } ?></td>
    <td>{PersonPrivilege_IsActual}</td>
    <td class="toolbar">
        <div id="ExpertHistory_{PersonPrivilege_id}_toolbar" class="toolbar">
            <a id="ExpertHistory_{PersonPrivilege_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
        </div>
    </td>
</tr>
