<div style="clear:both;" class="NewStyleDoc" id="EvnXmlProtokol_{EvnXml_id}"
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) {
	 	document.getElementById('EvnXmlProtokol_{EvnXml_id}_printdoc').style.display='block'
	 	document.getElementById('EvnXmlProtokol_{EvnXml_id}_emd').style.display='block'
	 }"
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) {
	 	document.getElementById('EvnXmlProtokol_{EvnXml_id}_printdoc').style.display='none'
	 	document.getElementById('EvnXmlProtokol_{EvnXml_id}_emd').style.display='none'
	 }"
	>
    <div class="vPrint vPrint-none"><input pid="{EvnXml_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnXmlProtokol" print="EvnXml" value="{EvnXml_id}"/></div>
    <div class="WrapDoc" id="EvnXmlProtokol_{EvnXml_id}_wrap">
        <div id="EvnXmlProtokol_{EvnXml_id}_content">
            <div class="EvnXml__menu">
                <span id="EvnXmlProtokol_{EvnXml_id}_showActions" class="button" style="margin:1px 0px 0px 4px;" title="Выбор действия"></span>
                <a id="EvnXmlProtokol_{EvnXml_id}_printdoc" style="display:none;float:right" class="button icon icon-print16__1" title="Печать на верхней половине листа">
                    <span></span>
                </a>
				<div class="emd-here" data-objectname="EvnXml" data-objectid="{EvnXml_id}" data-issigned="{EvnXml_IsSigned}" data-minsigncount="{EvnXml_MinSignCount}" data-signcount="{EvnXml_SignCount}" id="EvnXmlProtokol_{EvnXml_id}_emd" style="display:none;float:right"></div>
            </div>
			<span style="display: none;">{EvnXml_pid}</span>
            <span id="EvnXmlProtokol_{EvnXml_id}_showDoc" class="link" title="Показать документ">{EvnXml_Name}</span>
			<?php
			if((getRegionNick() !== 'msk')){
			?><b>Дата</b>: {EvnXml_Date} <?php
				} else {
				?><span id="EvnXmlProtokol_data_{EvnXml_id}_inputEvnXmlProtokol" dataid="{EvnXml_id}" datasetdt="{EvnXml_setDT}" style="vertical-align:middle"><?php if(!$EvnXml_setDTDate) { ?>Дата проведения: {EvnXml_setDTDate} Время: {EvnXml_setDTTime} <?php } ?> <span id="EvnXmlProtokol_{EvnXml_id}_addDateMenu" style="display:inline-block;background: url('/img/calendar/silk/calendar_edit.png') no-repeat center center;float:none;width: 22px;height: 16px;border: 1px solid #99bbe8;border-radius: 3px;min-width:10px;min-height: 20px; vertical-align: middle;" dataid="{EvnXml_id}" dataname="" class="datefield_addDateMenu" title="<?php if($EvnXml_setDTDate) { ?>Добавить<?php }else{ ?>Редактировать<?php } ?> дату и время"></span></span><span id="EvnXmlProtokol_data_{EvnXml_id}_inputareaEvnXmlProtokol" style="vertical-align:middle"></span> <?php
				}
			?><b>Автор</b>: {pmUser_Name}
        </div>
        <div id="EvnXmlProtokol_Data_{EvnXml_id}" class="EvnXml__fullDoc">
            {EvnXml_Data}
            <div style="text-align: center; width: 95%">
                <span id="EvnXmlProtokol_{EvnXml_id}_showActions2" style="display: block;" class="button" title="Выбор действия"></span>
                <span id="EvnXmlProtokol_{EvnXml_id}_hideDoc" class="link">Свернуть документ</span>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>