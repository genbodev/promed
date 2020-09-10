<div id="PersonSvidInfo_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonSvidInfo_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonSvidInfo_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Свидетельства</h2>
        <div id="PersonSvidInfo_{pid}_toolbar" class="toolbar">
        </div>
    </div>

    <table>

        <col style="width: 25%" class="first" />
        <col style="width: 25%" />
        <col style="width: 25%"/>
        <col class="last" />
        <col class="toolbar">

        <thead>
        <tr>
            <th>Тип свидетельства</th>
            <th>Серия</th>
            <th>Номер</th>
            <th>Дата выдачи</th>
            <th class="toolbar">
        </tr>
        </thead>

        <tbody id="PersonSvidInfoList_{pid}">

        {items}

        </tbody>

    </table>

</div>
