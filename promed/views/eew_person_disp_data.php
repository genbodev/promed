<div id="PersonDispInfo_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonDispInfo_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonDispInfo_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Диспансерный учет</h2>
        <div id="PersonDispInfo_{pid}_toolbar" class="toolbar">
            <a id="PersonDispInfoList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
            <a id="PersonDispInfo_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

        <col style="width: 10%" class="first" />
        <col style="width: 10%" />
        <col />
        <col style="width: 10%"/>
        <col style="width: 20%"/>
		<col />
        <col class="last" />
        <col class="toolbar">

        <thead>
            <tr>
                <th>Дата постановки на учет</th>
                <th>Шифр МКБ</th>
                <th>Диагноз</th>
                <th>Дата снятия с учета</th>
                <th>Причина снятия с учета</th>
                <th>Профиль/Врач</th>
                <th>ЭЦП</th>
                <th class="toolbar">
            </tr>
        </thead>

        <tbody id="PersonDispInfoList_{pid}">

            {items}

        </tbody>

    </table>

</div>
