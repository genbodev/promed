<?php $isKz = (getRegionNick() == 'kz'); ?>
<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptKardio_{EvnRecept_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptKardio_{EvnRecept_id}_toolbar').style.display='none'">
    <td class="vPrint vPrint-none" width="1"><input pid="{EvnRecept_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnRecept" print="EvnRecept" value="{EvnRecept_id}"/></td>
	<td>
        <div id="EvnReceptKardio_{EvnRecept_id}">
            <div id="EvnReceptKardio_{EvnRecept_id}_content"><b><?php if(!$isKz){ ?>Серия</b> {EvnRecept_Ser} <?php } ?><b>Номер</b>: {EvnRecept_Num} <b>Препарат</b>: {Drug_Name} <b>Количество</b>: {EvnRecept_Kolvo} <b>{EvnRecept_IsDelivery}</b></div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnReceptKardio_{EvnRecept_id}_toolbar" class="toolbar">
			<?php if(!empty($ReceptType_Code) && $ReceptType_Code == 2){ ?>
            <a id="EvnReceptKardio_{EvnRecept_id}_print" class="button icon icon-print16" title="Печать рецепта"><span></span></a>
			<?php } ?>
			<?php if (!$isMseDepers) { ?><a id="EvnReceptKardio_{EvnRecept_id}_view" class="button icon icon-view16" title="Просмотр рецепта"><span></span></a><?php }?>
            <a id="EvnReceptKardio_{EvnRecept_id}_delete" class="button icon icon-delete16" title="Удалить рецепт"><span></span></a>
			<div class="emd-here" data-objectname="EvnRecept" data-objectid="{EvnRecept_id}" data-issigned="{EvnRecept_IsSigned}"></div>
        </div>
    </td>
</tr>
