<div id="FeedingType_{pid}" class="data-table component" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('FeedingType_{pid}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('FeedingType_{pid}_toolbar').style.display='none'">

    <div class="caption">
        <h2>Cпособ вскармливания</h2>
        <div id="FeedingType_{pid}_toolbar" class="toolbar">
            <a id="FeedingTypeList_{pid}_add" class="button icon icon-add16" title="Добавить"><span></span></a>
            <a id="FeedingType_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </div>

    <table>

        <col style="width: 15%" class="first" />
        <col class="last">
        <col class="toolbar"/>

        <thead>
        <tr>

            <th>Возраст(мес)</th>
            <th>Вид вскармливания</th>
            <th class="toolbar"></th>
        </tr>
        </thead>

        <tbody id="FeedingTypeList_{pid}">

        {items}

        </tbody>

    </table>

</div>
