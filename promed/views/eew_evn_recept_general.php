<div id="EvnReceptGeneralList_{pid}" <?php if (empty($items)) { echo "style='display: none;'"; } ?>class="data-table"
onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptGeneralList_{pid}_toolbar').style.display='block'" 
onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptGeneralList_{pid}_toolbar').style.display='none'">
    <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnRecept')"/>
	<div class="caption">
        <h2><span id="EvnReceptGeneralList_{pid}_toggleDisplay" class="collapsible">Рецепты за полную стоимость</span></h2>
        <div id="EvnReceptGeneralList_{pid}_toolbar" class="toolbar">
        </div>
    </div>

    <table id="EvnReceptGeneralTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

		<col class="vPrint vPrint-none" />
        <col class="first last" />
        <col class="toolbar"/>

        {items}

    </table>

</div>