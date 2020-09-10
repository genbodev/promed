<div id="EvnXmlEpikrizList_{pid}" class="data-table"
     onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnXmlEpikrizList_{pid}_toolbar').style.display='block'"
     onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnXmlEpikrizList_{pid}_toolbar').style.display='none'"
     style="margin-bottom: 0;">
    <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnXmlEpirkiz')"/>
	<div class="caption">
        <h2><span id="EvnXmlEpikrizList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Эпикризы</span></h2>
        <div id="EvnXmlEpikrizList_{pid}_toolbar" class="toolbar">
			<a id="EvnXmlEpikrizList_{pid}_adddoc" class="button icon icon-add16" title="Добавить документ"><span></span></a>
        </div>
    </div>
</div>