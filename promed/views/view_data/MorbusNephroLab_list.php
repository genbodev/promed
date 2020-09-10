<div id="MorbusNephroLabList_{MorbusNephro_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroLabList_{MorbusNephro_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusNephroLabList_{MorbusNephro_pid}_{pid}_toolbar').style.display='none'">
    <div class="caption">
        <h2><span id="MorbusNephroLabList_{MorbusNephro_pid}_{pid}_toggleDisplay" class="<?php
        if (!empty($items)) { ?>collapsible<?php }
        ?>">Лабораторные исследования</span></h2>
        <div id="MorbusNephroLabList_{MorbusNephro_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusNephroLabList_{MorbusNephro_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
	        <a id="MorbusNephroLabList_{MorbusNephro_pid}_{pid}_selectIsLast" class="link viewAll">Отображать только последние</a>
        </div>
    </div>
    <table id="MorbusNephroLabTable_{MorbusNephro_pid}_{pid}" style="display: <?php
    if (empty($items)) { echo 'none'; } else { echo 'block'; }
    ?>;">
        <col class="first" />
        <col />
        <col class="last" />
        <col class="toolbar"/>
        <thead>
        <tr>
            <th>Дата</th>
            <th>Показатель</th>
            <th>Значение</th>
            <th class="toolbar"></th>
        </tr>
        </thead>
        <tbody>
        {items}
        </tbody>
    </table>
</div>