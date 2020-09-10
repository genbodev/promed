<?php
$is_allow_edit = ($accessType == 'edit' && empty($isMainRec));
?>
<tr id="EvnDiagNephro_{MorbusNephro_pid}_{EvnDiagNephro_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagNephro_{MorbusNephro_pid}_{EvnDiagNephro_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagNephro_{MorbusNephro_pid}_{EvnDiagNephro_id}_toolbar').style.display='none'">
    <td>{EvnDiagNephro_setDate}</td>
    <td>{Diag_Code} {Diag_Name}</td>
    <td class="toolbar">
        <div id="EvnDiagNephro_{MorbusNephro_pid}_{EvnDiagNephro_id}_toolbar" class="toolbar">
            <a id="EvnDiagNephro_{MorbusNephro_pid}_{EvnDiagNephro_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
            <a id="EvnDiagNephro_{MorbusNephro_pid}_{EvnDiagNephro_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
        </div>
    </td>
</tr>
