<?php
$is_allow_edit = ($accessType == 'edit');
$isUfa = getRegionNick() == 'ufa';
$empty_str = "";
?>
<tr id="MorbusNephroDisp_{MorbusNephro_pid}_{MorbusNephroDisp_id}"
        class="list-item"
        onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDisp_{MorbusNephro_pid}_{MorbusNephroDisp_id}_toolbar').style.display='block'"
        onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDisp_{MorbusNephro_pid}_{MorbusNephroDisp_id}_toolbar').style.display='none'">
    <td> {MorbusNephroDisp_Date}<?php echo (empty($MorbusNephroDisp_EndDate) || $MorbusNephroDisp_EndDate == "01.01.1900") ? "" : " - " . $MorbusNephroDisp_EndDate; ?> </td>
    <td>{RateType_Name}</td>
    <td>{Rate_ValueStr}</td>
<?php if($isUfa) { ?> <!-- #135648 -->
    <td> <?php  echo (empty($CreatinineUnitType_Name)) ? $empty_str : $CreatinineUnitType_Name; ?></td>
    <td> <?php  echo (empty($NephroCkdEpi_value)) ? $empty_str : $NephroCkdEpi_value; ?> </td>
<?php  } ?>
    <td class="toolbar">
        <div id="MorbusNephroDisp_{MorbusNephro_pid}_{MorbusNephroDisp_id}_toolbar" class="toolbar">
            <a id="MorbusNephroDisp_{MorbusNephro_pid}_{MorbusNephroDisp_id}_edit" class="button icon icon-edit16" title="Редактировать"<?php echo (($is_allow_edit)?'':' style="display: none;"'); ?>><span></span></a>
        </div>
    </td>
</tr>
