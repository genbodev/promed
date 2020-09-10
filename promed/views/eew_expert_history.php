<div id="ExpertHistory_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('ExpertHistory_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('ExpertHistory_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Экспертный анамнез и льготы</h2>
        <div id="ExpertHistory_{pid}_toolbar" class="toolbar">
            <a id="ExpertHistoryList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
            <a id="ExpertHistory_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

        <col class="first" />
        <col style="width: 10%" />
        <col style="width: 10%" />
        <col class="last" />
        <col class="toolbar"/>

        <thead>
            <tr>
                <th>Категория льготы</th>
                <th>Дата открытия</th>
                <th>Дата закрытия</th>
                <th>Актуальность</th>
                <th class="toolbar"></th>
            </tr>
        </thead>

        <tbody id="ExpertHistoryList_{pid}">

            {items}            

        </tbody>

    </table>

</div>
