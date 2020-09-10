<div style="clear:both;" class="NewStyleDoc" id="EvnXmlRecord_{EvnXml_id}" 
    onmouseover="if (isMouseLeaveOrEnter(event, this)) {
        document.getElementById('EvnXmlRecord_{EvnXml_id}_printrecordtop').style.display='block';
        document.getElementById('EvnXmlRecord_{EvnXml_id}_printrecordbot').style.display='block';
        document.getElementById('EvnXmlRecord_{EvnXml_id}_emd').style.display='block';
    }"
    onmouseout="if (isMouseLeaveOrEnter(event, this)) {
        document.getElementById('EvnXmlRecord_{EvnXml_id}_printrecordtop').style.display='none';
        document.getElementById('EvnXmlRecord_{EvnXml_id}_printrecordbot').style.display='none';
        document.getElementById('EvnXmlRecord_{EvnXml_id}_emd').style.display='none';
    }"
    >
    <div class="vPrint vPrint-none"><input pid="{EvnXml_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnXmlRecord" print="EvnXml" value="{EvnXml_id}"/></div>
	<div class="WrapDoc" id="EvnXmlRecord_{EvnXml_id}_wrap">
        <div id="EvnXmlRecord_{EvnXml_id}_content">
            <div class="EvnXml__menu">
                <span id="EvnXmlRecord_{EvnXml_id}_showActions" class="button" style="margin:1px 0px 0px 4px;" title="Выбор действия"></span>
                <a id="EvnXmlRecord_{EvnXml_id}_printrecordtop" style="display:none;float:right;" class="button icon icon-print16__1" title="Печать на верхней половине листа">
                    <span></span>
                </a>
                <a id="EvnXmlRecord_{EvnXml_id}_printrecordbot" style="display:none;float:right;" class="button icon icon-print16__2" title="Печать на нижней половине листа">
                    <span></span>
                </a>
				<div class="emd-here" data-objectname="EvnXml" data-objectid="{EvnXml_id}" data-issigned="{EvnXml_IsSigned}" data-minsigncount="{EvnXml_MinSignCount}" data-signcount="{EvnXml_SignCount}" id="EvnXmlRecord_{EvnXml_id}_emd" style="display:none;float:right"></div>
            </div>
			<span style="display:none;">{EvnXml_pid}</span>
            <span id="EvnXmlRecord_{EvnXml_id}_showDoc" class="link" title="Показать документ">{EvnXml_Name}</span>
			<?php
			if((getRegionNick() !== 'msk')){
				?><b>Дата</b>: {EvnXml_Date} <?php
			} else {
				?><span id="EvnXmlRecord_data_{EvnXml_id}_inputEvnXmlRecord" dataid="{EvnXml_id}" datasetdt="{EvnXml_setDT}" style="vertical-align:middle"><?php if($EvnXml_setDTDate) { ?>Дата проведения: {EvnXml_setDTDate} Время: {EvnXml_setDTTime} <?php } ?> <span id="EvnXmlRecord_{EvnXml_id}_addDateMenu" dataid="{EvnXml_id}" style="display:inline-block;background: url('/img/calendar/silk/calendar_edit.png') no-repeat center center;float:none;width: 22px;height: 16px;border: 1px solid #99bbe8;border-radius: 3px;min-width:10px;min-height: 20px; vertical-align: middle;" class="datefield_addDateMenu" title="<?php if(!$EvnXml_setDTDate) { ?>Добавить<?php }else{ ?>Редактировать<?php } ?> дату и время"></span></span><span id="EvnXmlRecord_data_{EvnXml_id}_inputareaEvnXmlRecord" style="vertical-align:middle"></span> <?php
			}
			?><b>Автор</b>: {pmUser_Name}
        </div>
        <div id="EvnXmlRecord_Data_{EvnXml_id}" class="EvnXml__fullDoc">
            {EvnXml_Data}
            <div style="text-align:center;width:95%;">
                <span id="EvnXmlRecord_{EvnXml_id}_showActions2" style="display: block;" class="button" title="Выбор действия"></span>
                <span id="EvnXmlRecord_{EvnXml_id}_hideDoc" class="link">Свернуть документ</span>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>