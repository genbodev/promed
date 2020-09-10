<div id="EvnDiagNephroList_{MorbusNephro_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagNephroList_{MorbusNephro_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagNephroList_{MorbusNephro_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="EvnDiagNephroList_{MorbusNephro_pid}_{pid}_toggleDisplay" class="<?php
        if (!empty($items)) { ?>collapsible<?php }
        ?>">Диагноз</span></h2>
        <div id="EvnDiagNephroList_{MorbusNephro_pid}_{pid}_toolbar" class="toolbar">
            <a id="EvnDiagNephroList_{MorbusNephro_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
        </div>
    </div>
    <table id="EvnDiagNephroTable_{MorbusNephro_pid}_{pid}" style="display: block;">
        <col class="first" />
        <col class="last" />
        <col class="toolbar"/>
        <thead>
        <tr>
            <th>Дата установления</th>
            <th>Диагноз</th>
            <th class="toolbar"></th>
        </tr>
        </thead>
        <tbody>
        {items}
        </tbody>
    </table>
</div>