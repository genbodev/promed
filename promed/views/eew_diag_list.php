<div id="DiagList_{pid}" class="data-table component read-only" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DiagList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('DiagList_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Список уточненных диагнозов</h2>
        <div id="DiagList_{pid}_toolbar" class="toolbar">
            <a id="DiagList_{pid}_addDiag" class="button icon icon-add16" title="Добавить"><span></span></a>
			<a id="DiagList_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
		</div>
    </div>

    <table>

        <col style="width: 10%" class="first" />
        <col style="width: 10%" />
        <col />
        <col style="width: 20%"/>
        <col class="last" />

        <thead>
            <tr>
                <th>Дата установки</th>
                <th>Шифр МКБ</th>
                <th>Диагноз</th>
                <th>ЛПУ</th>
                <th>Профиль/Врач</th>
            </tr>
			
        </thead>

        <tbody id="DiagListList_{pid}">

            {items}

        </tbody>

    </table>

</div>
