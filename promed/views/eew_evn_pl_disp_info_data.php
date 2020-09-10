<div id="EvnPLDispInfo_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispInfo_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispInfo_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Диспансеризация/мед. осмотры</h2>
        <div id="EvnPLDispInfo_{pid}_toolbar" class="toolbar">
            <a id="EvnPLDispInfo_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

		<col style="width: 30%" class="first" />
		<col style="width: 10%" />
		<col style="width: 10%" />
		<col />
		<col />
		<col class="last" />
		<col class="toolbar" />

        <thead>
            <tr>
                <th>Тип</th>
                <th>Дата начала</th>
                <th>Дата окончания</th>
                <th>МО проведения</th>
                <th>Группа здоровья</th>
				<th>Диагноз, установленный впервые</th>
                <th class="toolbar">
            </tr>
        </thead>

        <tbody id="EvnPLDispInfoList_{pid}">

            {items}

        </tbody>

    </table>

</div>
