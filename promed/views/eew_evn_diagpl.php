<div id="EvnDiagPLList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPLList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPLList_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="EvnDiagPLList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Сопутствующие диагнозы</span></h2>
        <div id="EvnDiagPLList_{pid}_toolbar" class="toolbar">
            <a id="EvnDiagPLList_{pid}_add" class="button icon icon-add16" title="Добавить диагноз"><span></span></a>
        </div>
    </div>

    <table id="EvnDiagPLTable_{pid}" style="display: block;"><!-- class="allowed_hide_after_loading"  -->

        <col class="first last" />
        <col class="toolbar"/>

    	{items}

    </table>

</div>
