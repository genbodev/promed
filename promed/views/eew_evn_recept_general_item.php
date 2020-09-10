<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptGeneral_{EvnReceptGeneral_id}_toolbar').style.display='block'" 
onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptGeneral_{EvnReceptGeneral_id}_toolbar').style.display='none'">
    <td class="vPrint vPrint-none" width="1"><input pid="{EvnRecept_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnRecept" print="EvnReceptGeneral" value="{EvnReceptGeneral_id}"/></td>
	<td>
        <div id="EvnReceptGeneral_{EvnReceptGeneral_id}">
            <div id="EvnReceptGeneral_{EvnReceptGeneral_id}_content">{ReceptForm_Name} <b>Серия</b> {EvnReceptGeneral_Ser} <b>Номер</b>: {EvnReceptGeneral_Num} {drugs}<br><b>Препарат</b>: {Drug_Name} <b>Количество</b>: {EvnReceptGeneral_Kolvo}{/drugs} <b>{EvnReceptGeneral_IsDelivery}</b></div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnReceptGeneral_{EvnReceptGeneral_id}_toolbar" class="toolbar">
			<?php if (!$isMseDepers) { ?><a id="EvnReceptGeneral_{EvnReceptGeneral_id}_view" class="button icon icon-view16" title="Просмотр рецепта"><span></span></a><?php }?>
			<?php if ($ReceptType_Code == 3) { ?><div class="emd-here" data-objectname="EvnReceptGeneral" data-objectid="{EvnReceptGeneral_id}" data-issigned="{EvnReceptGeneral_IsSigned}"></div><?php } ?>
        </div>
    </td>
</tr>
