<div id="SurgicalList_{pid}" class="data-table component read-only" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('SurgicalList_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('SurgicalList_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Список оперативных вмешательств</h2>
        <div id="SurgicalList_{pid}_toolbar" class="toolbar">
            <a id="SurgicalList_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

        <col style="width: 10%" class="first" />
        <col />
        <col style="width: 10%" />
        <col style="width: 45%" class="last" />

        <thead>
            <tr>
                <th>Дата</th>
                <th>ЛПУ</th>
                <th>Код услуги</th>
                <th>Услуга</th>
            </tr>
        </thead>

        <tbody id="SurgicalListList_{pid}">

            {items}

        </tbody>

    </table>

</div>
