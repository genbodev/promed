<div id="EvnUslugaStacList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStacList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStacList_{pid}_toolbar').style.display='none'">
    <input type="checkbox" value="{pid}" class="checkMain vPrint vPrint-none" onclick="Ext.getCmp('PersonEmkForm').checkAll(this,'chkEvnUsluga')"/>
	<div class="caption">
        <h2>Услуги</h2>
        <div id="EvnUslugaStacList_{pid}_toolbar" class="toolbar">
            <a id="EvnUslugaStacList_{pid}_add" class="button icon icon-add16" title="Оформить оказание услуги"><span></span></a>
        </div>
    </div>

    <table>

		<col class="vPrint vPrint-none" />
        <col class="first last" />
        <col class="toolbar"/>

    	{items}

    </table>

</div>
