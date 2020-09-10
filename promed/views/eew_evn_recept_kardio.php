<div id="EvnReceptKardioList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptKardioList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptKardioList_{pid}_toolbar').style.display='none'">
    <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnReceptKardio')"/>
	<div class="caption">
        <h2><span id="EvnReceptKardioList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Рецепты. ЛКО (кардиопрограмма)</span></h2>
        <div id="EvnReceptKardioList_{pid}_toolbar" class="toolbar">
            <a id="EvnReceptKardioList_{pid}_add" class="button icon icon-add16" title="Добавить рецепт"><span></span></a>
        </div>
    </div>

    <table id="EvnReceptKardioTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="vPrint vPrint-none" />
        <col class="first last" />
        <col class="toolbar"/>

        {items}

    </table>

</div>
