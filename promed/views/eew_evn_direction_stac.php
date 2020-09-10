<div id="EvnDirectionStacList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionStacList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirectionStacList_{pid}_toolbar').style.display='none'">
   <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnDirection')"/>
	<div class="caption">
        <h2><span id="EvnDirectionStacList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Направления</span></h2>
        <div id="EvnDirectionStacList_{pid}_toolbar" class="toolbar">
            <a id="EvnDirectionStacList_{pid}_add" class="button icon icon-add16" title="Создать направление"><span></span></a>
        </div>
    </div>

    <table id="EvnDirectionStacTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="vPrint vPrint-none" />
        <col class="first last" />
        <col class="toolbar"/>

        {items}

        {item_arr}
    	{/item_arr}

    </table>

</div>
