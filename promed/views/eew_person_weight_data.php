<div id="PersonWeight_{pid}" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonWeight_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('PersonWeight_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h3>Масса</h3>
        <div class="toolbar" id="PersonWeight_{pid}_toolbar">
            <a id="PersonWeightList_{pid}_addPersonWeight" class="button icon icon-add16" title="Добавить"><span></span></a>
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
                <th>Масса (кг)</th>
                <th>Отклонение</th>
                <th>ИМТ</th>
                <th class="toolbar"></th>
            </tr>
        </thead>

        <tbody id="PersonWeightList_{pid}">
            
            {items}
            
        </tbody>

    </table>
</div>
