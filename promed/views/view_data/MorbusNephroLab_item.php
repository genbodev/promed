<?php
$is_allow_edit = ($accessType == 'edit');
?>
<tr id="MorbusNephroLab_{MorbusNephro_pid}_{MorbusNephroLab_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroLab_{MorbusNephro_pid}_{MorbusNephroLab_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroLab_{MorbusNephro_pid}_{MorbusNephroLab_id}_toolbar').style.display='none'">
    <td>{MorbusNephroLab_Date}</td>
    <td>{RateType_Name}</td>
    <td>{Rate_ValueStr}</td>
    <td class="toolbar">
        <div id="MorbusNephroLab_{MorbusNephro_pid}_{MorbusNephroLab_id}_toolbar" class="toolbar">
            <a id="MorbusNephroLab_{MorbusNephro_pid}_{MorbusNephroLab_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
            <a id="MorbusNephroLab_{MorbusNephro_pid}_{MorbusNephroLab_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
        </div>
    </td>
</tr>
