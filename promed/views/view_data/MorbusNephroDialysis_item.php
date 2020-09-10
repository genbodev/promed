<?php
$is_allow_edit = ($accessType == 'edit');
$empty_str = "";
?>
<tr id="MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}_toolbar').style.display='none'">
	<td>{Lpu_Nick}</td>
	<td>{MorbusNephroDialysis_begDT}</td>
    <td>{MorbusNephroDialysis_endDT}</td>
    <td>{PersonRegisterOutCause_Name}</td>
    <td class="toolbar">
        <div id="MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}_toolbar" class="toolbar">
			<a id="MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
            <a id="MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}_editout" class="button icon icon-clear16" title="Исключить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
			<a id="MorbusNephroDialysis_{MorbusNephro_pid}_{MorbusNephroDialysis_id}_delete" class="button icon icon-delete16" title="Удалить"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
        </div>
    </td>
</tr>
