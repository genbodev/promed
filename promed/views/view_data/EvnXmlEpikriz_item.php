<div style="clear:both;" class="NewStyleDoc" id="EvnXmlEpikriz_{EvnXml_id}"
	 onmouseover="if (isMouseLeaveOrEnter(event, this)) {
	 	document.getElementById('EvnXmlEpikriz_{EvnXml_id}_printdoc').style.display='block'
	 	document.getElementById('EvnXmlEpikriz_{EvnXml_id}_emd').style.display='block'
	 }"
	 onmouseout="if (isMouseLeaveOrEnter(event, this)) {
	 	document.getElementById('EvnXmlEpikriz_{EvnXml_id}_printdoc').style.display='none'
	 	document.getElementById('EvnXmlEpikriz_{EvnXml_id}_emd').style.display='none'
	 }"
	>
    <div class="vPrint vPrint-none"><input pid="{EvnXml_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnXmlEpikriz" print="EvnXml" value="{EvnXml_id}"/></div>
	<div class="WrapDoc" id="EvnXmlEpikriz_{EvnXml_id}_wrap">
        <div id="EvnXmlEpikriz_{EvnXml_id}_content">
            <div class="EvnXml__menu">
                <span id="EvnXmlEpikriz_{EvnXml_id}_showActions" class="button" style="margin:1px 0px 0px 4px;" title="Выбор действия"></span>
                <a id="EvnXmlEpikriz_{EvnXml_id}_printdoc" style="display:none;float:right" class="button icon icon-print16__1" title="Печать на верхней половине листа">
                    <span></span>
                </a>
				<div class="emd-here" data-objectname="EvnXml" data-objectid="{EvnXml_id}" data-issigned="{EvnXml_IsSigned}" data-minsigncount="{EvnXml_MinSignCount}" data-signcount="{EvnXml_SignCount}" id="EvnXmlEpikriz_{EvnXml_id}_emd" style="display:none;float:right"></div>
            </div>
			<span style="display: none;">{EvnXml_pid}</span>
            <span id="EvnXmlEpikriz_{EvnXml_id}_showDoc" class="link" title="Показать документ">{EvnXml_Name}</span>
			<?php
			if((getRegionNick() !== 'msk')){
				?><b>Дата</b>: {EvnXml_Date} <?php
			} else {
				?><span id="EvnXmlEpikriz_data_{EvnXml_id}_inputEvnXmlEpikriz" dataid="{EvnXml_id}" datasetdt="{EvnXml_setDT}" style="vertical-align:middle"><?php if($EvnXml_setDTDate) { ?>Дата проведения: {EvnXml_setDTDate} Время: {EvnXml_setDTTime} <?php } ?> <span id="EvnXmlEpikriz_{EvnXml_id}_addDateMenu" dataid="{EvnXml_id}" style="display:inline-block;background: url('/img/calendar/silk/calendar_edit.png') no-repeat center center;float:none;width: 22px;height: 16px;border: 1px solid #99bbe8;border-radius: 3px;min-width:10px;min-height: 20px; vertical-align: middle;" class="datefield_addDateMenu" title="<?php if(!$EvnXml_setDTDate) { ?>Добавить<?php }else{ ?>Редактировать<?php } ?> дату и время"></span></span><span id="EvnXmlEpikriz_data_{EvnXml_id}_inputareaEvnXmlEpikriz" style="vertical-align:middle"></span> <?php
			}
			?><b>Автор</b>: {pmUser_Name}
        </div>
        <div id="EvnXmlEpikriz_Data_{EvnXml_id}" class="EvnXml__fullDoc">
            {EvnXml_Data}
            <div style="text-align: center; width: 95%">
                <span id="EvnXmlEpikriz_{EvnXml_id}_showActions2" style="display: block;" class="button" title="Выбор действия"></span>
                <span id="EvnXmlEpikriz_{EvnXml_id}_hideDoc" class="link">Свернуть документ</span>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>