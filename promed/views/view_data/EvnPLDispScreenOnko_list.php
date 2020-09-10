<div id="EvnPLDispScreenOnkoList_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispScreenOnkoList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispScreenOnkoList_{pid}_toolbar').style.display='none'" style='margin-bottom:0;'>
    <div class="caption">
        <h2><span id="EvnPLDispScreenOnkoList_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Скрининговые обследования</span></h2>
        <div id="EvnPLDispScreenOnkoList_{pid}_toolbar" class="toolbar">
            <a id="EvnPLDispScreenOnkoList_{pid}_adddoc" class="button icon icon-add16" title="Добавить скрининг по онкологии"><span></span></a>
        </div>
    </div>
    <table id="EvnPLDispScreenOnkoTable_{pid}">
        <thead>
            <col class="first" />
            <col style="width: 20%" class="last" />
            <tr>
                <th>Наименование</th>
                <th>Дата прохождения</th>
            </tr>
        </thead>
        <tbody>
            {items}
        </tbody>
    </table>
</div>
