<div id="PersonHeight_{pid}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonHeight_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonHeight_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h3>Рост</h3>
        <div class="toolbar" id="PersonHeight_{pid}_toolbar">
            <a id="PersonHeightList_{pid}_addPersonHeight" class="button icon icon-add16" title="Добавить"><span></span></a>
        </div>
    </div>

    <table>

        <col style="width: 10%" class="first" />
        <col style="width: 20%"/>
        <col />
        <col class="last" />
        <col class="toolbar"/>

        <thead>
            <tr>
                <th>Дата измерения</th>
                <th>Вид замера</th>
                <th>Рост (см)</th>
                <th>Отклонение</th>
                <th class="toolbar"></th>
            </tr>
        </thead>

        <tbody id="PersonHeightList_{pid}">

            {items}

        </tbody>

    </table>
</div>
