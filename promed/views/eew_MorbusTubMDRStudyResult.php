<div id="MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}" class="data-table" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2><span id="MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}_toggleDisplay" class="<?php if (!empty($items)) { ?>collapsible<?php } ?>">Результаты исследований</span></h2>
        <div id="MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}_toolbar" class="toolbar">
            <a id="MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
            <a id="MorbusTubMDRStudyResultList_{MorbusTub_pid}_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table id="MorbusTubMDRStudyResultTable_{MorbusTub_pid}_{pid}" style="display: <?php echo (empty($items))?'none':'block'; ?>;">
        <col class="first" />
        <col />
        <col />
        <col />
        <col class="last" />
        <col class="toolbar"/>
        <thead>
        <tr>
            <th>месяц лечения</th>
            <th>дата сбора бактериологического исследования</th>
            <th>дата результата на ТЛЧ</th>
            <th>результат рентгенологического обследования</th>
            <th>примечание</th>
            <th class="toolbar"></th>
        </tr>
        </thead>
        <tbody>
        {items}
        </tbody>
    </table>

</div>