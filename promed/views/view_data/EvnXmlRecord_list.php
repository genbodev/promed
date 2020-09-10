<div id="EvnXmlRecordList_{pid}" class="data-table"
     onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnXmlRecordList_{pid}_toolbar').style.display='block'"
     onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnXmlRecordList_{pid}_toolbar').style.display='none'"
     style="margin-bottom: 0;">
    <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnXmlRecord')"/>
	<div class="caption">
        <h2><span id="EvnXmlRecordList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Дневниковые записи</span></h2>
        <div id="EvnXmlRecordList_{pid}_toolbar" class="toolbar">
			<a id="EvnXmlRecordList_{pid}_adddoc" class="button icon icon-add16" title="Добавить документ"><span></span></a>
        </div>
    </div>
</div>