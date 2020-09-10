<div id="MorbusNephroDialysisList_{MorbusNephro_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDialysisList_{MorbusNephro_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroDialysisList_{MorbusNephro_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusNephroDialysisList_{MorbusNephro_pid}_{pid}_toggleDisplay" class="<?php
        if (!empty($items)) { ?>collapsible<?php }
        ?>">Нуждается в диализе</span></h2>
        <div id="MorbusNephroDialysisList_{MorbusNephro_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusNephroDialysisList_{MorbusNephro_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
        </div>
    </div>
    <table id="MorbusNephroDialysisTable_{MorbusNephro_pid}_{pid}" style="display: <?php
    if (empty($items)) { echo 'none'; } else { echo 'block'; }
    ?>;">
        <col class="first" />
        <col />
        <col class="last" />
        <col class="toolbar"/>
        <thead>
        <tr>
            <th>МО</th>
            <th>Дата включения в список</th>
            <th>Дата исключения из списка</th>
            <th>Причина исключения</th>
            <th class="toolbar"></th>
        </tr>
        </thead>
        <tbody>
        {items}
        </tbody>
    </table>
</div>