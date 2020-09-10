
<div id="EvnDirectionList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionList_{pid}_toolbar').style.display='none'">
   <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnDirection')"/>
	<div class="caption">
		
       <h2><span id="EvnDirectionList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Направления</span></h2>
        <div id="EvnDirectionList_{pid}_toolbar" class="toolbar">
            <a id="EvnDirectionList_{pid}_add" class="button icon icon-add16" title="Создать направление"><span></span></a>
			<a id="EvnDirectionList_{pid}_addtome" class="button icon" title="Записать к себе"><span style="padding-left: 2px;">Записать к себе</span></a>
        </div>
    </div>

    <table id="EvnDirectionTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->
		
		<col class="vPrint vPrint-none" />
        <col class="first last" />
        <col class="toolbar"/>

        {items}

        {item_arr}
    	{/item_arr}

    </table>

</div>
