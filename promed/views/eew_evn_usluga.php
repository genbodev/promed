<div id="EvnUslugaList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaList_{pid}_toolbar').style.display='none'">
    <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnUsluga')"/>
	<div class="caption">
        <h2><span id="EvnUslugaList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Услуги</span></h2>
        <div id="EvnUslugaList_{pid}_toolbar" class="toolbar">
            <a id="EvnUslugaList_{pid}_add" class="button icon icon-add16" title="Оформить оказание услуги"><span></span></a>
        </div>
    </div>

    <table id="EvnUslugaTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

        <col class="vPrint vPrint-none" />
		<col class="first last" />
        <col class="toolbar"/>

    	{items}

    </table>

</div>
