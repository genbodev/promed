<div id="MantuReaction_{pid}"
  class="data-table component read-only"
  onmouseover=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('MantuReaction_{pid}_toolbar').style.display='block'"
  onmouseout=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('MantuReaction_{pid}_toolbar').style.display='none'">

  <div class="caption">
    <h2>Манту/Диаскинтест</h2>
    <div id="MantuReaction_{pid}_toolbar" class="toolbar">
      <a id="MantuReaction_{pid}_print" class="button icon icon-print16" title="Печать"><span></span></a>
      <a id="MantuReaction_{pid}_viewKard063" class="button icon vac-plan16" title="Открыть карту профилактических прививок"><span></span></a>
    </div>
  </div>

  <table>

    <col style="width: 10%" class="first" />
    <col style="width: 10%" />
    <col style="width: 10%" />
    <col style="width: 10%" />
    <col style="width: 10%" />
    <col style="width: 30%" />
    <col style="width: 10%" class="last" />

    <thead>
    <tr>
      <th>Статус</th>
      <th>Дата вакцинации</th>
      <th>Метод диагностики</th>
      <th>Тип реакции</th>
      <th>Описание реакции</th>
      <th>Реакция, [мм]</th>
      <th>Наименование МО</th>
    </tr>
    </thead>

    <tbody id="MantuReactionList_{pid}">

    {items}

    </tbody>

  </table>

</div>
