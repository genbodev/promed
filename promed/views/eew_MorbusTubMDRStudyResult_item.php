<?php
$is_allow_edit = ($accessType == 1);
?>
<tr id="MorbusTubMDRStudyResult_{MorbusTub_pid}_{MorbusTubMDRStudyResult_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRStudyResult_{MorbusTub_pid}_{MorbusTubMDRStudyResult_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRStudyResult_{MorbusTub_pid}_{MorbusTubMDRStudyResult_id}_toolbar').style.display='none'">
    <td>{MorbusTubMDRStudyResult_Month}</td>
    <td>{MorbusTubMDRStudyResult_setDT}</td>
    <td>{MorbusTubMDRStudyDrugResult_setDT}</td>
    <td>{TubXrayResultType_Name}</td>
    <td>{MorbusTubMDRStudyResult_Comment}</td>
    <td class="toolbar">
        <div id="MorbusTubMDRStudyResult_{MorbusTub_pid}_{MorbusTubMDRStudyResult_id}_toolbar" class="toolbar">
            <a id="MorbusTubMDRStudyResult_{MorbusTub_pid}_{MorbusTubMDRStudyResult_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
            <a id="MorbusTubMDRStudyResult_{MorbusTub_pid}_{MorbusTubMDRStudyResult_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
        </div>
    </td>
</tr>
