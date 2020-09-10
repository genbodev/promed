<?php
if(!empty($frame) && $frame){echo '<div class="frame" style="min-width: 750px;">';}else{echo "<div>";}
?>
<div style="clear:both;" class="NewStyleDoc" id="FreeDocument_{EvnXml_id}"
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('FreeDocument_{EvnXml_id}_printdoc').style.display='block'; document.getElementById('FreeDocument_{EvnXml_id}_emd').style.display='block'; }"
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('FreeDocument_{EvnXml_id}_printdoc').style.display='none'; document.getElementById('FreeDocument_{EvnXml_id}_emd').style.display='none'; }">
	<div class="vPrint vPrint-none"><input pid="{EvnXml_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnXml" print="EvnXml" value="{EvnXml_id}"/></div>
    <div class="WrapDoc" id="FreeDocument_{EvnXml_id}_wrap">
        <div id="FreeDocument_{EvnXml_id}_content" style="height:19px;">
			
            <span id="FreeDocument_{EvnXml_id}_showActions" class="button" title="Выбор действия"></span>
			<a id="FreeDocument_{EvnXml_id}_printdoc" style="display:none;float:right" class="button icon icon-print16" title="Печать документ"><span></span></a>
			<div class="emd-here" data-objectname="EvnXml" data-objectid="{EvnXml_id}" data-issigned="{EvnXml_IsSigned}" id="FreeDocument_{EvnXml_id}_emd" style="display:none;float:right"></div>
            <span style="display: none;">{EvnXml_pid}</span>
            <span id="FreeDocument_{EvnXml_id}_showDoc" class="link" title="Показать документ">{EvnXml_Name}</span>
            <b>Дата</b>: {EvnXml_Date} <b>Автор</b>: {pmUser_Name}
        </div>
        <div id="FreeDocument_Data_{EvnXml_id}" style='clear:both; margin-top:5px; width:740px; display: none;'>
            {EvnXml_Data}
            <div style="text-align: center; width: 95%">
                <span id="FreeDocument_{EvnXml_id}_showActions2" style="display: block;" class="button" title="Выбор действия"></span>
                <span id="FreeDocument_{EvnXml_id}_hideDoc" class="link">Свернуть документ</span>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>
</div>