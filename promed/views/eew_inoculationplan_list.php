<div id="InoculationPlan_{pid}" class="data-table component read-only"
  onmouseover=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('InoculationPlan_{pid}_toolbar').style.display='block'"
  onmouseout=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('InoculationPlan_{pid}_toolbar').style.display='none'">

  <div class="caption">
    <h2>Планируемые прививки</h2>
      <div id="InoculationPlan_{pid}_toolbar" class="toolbar">
        <a id="InoculationPlan_{pid}_print" class="button icon icon-print16"
          title="Печать">
          <span></span>
        </a>

        <?php if (getRegionNick() == 'vologda') { ?>
          <a id="InoculationPlan_{pid}_viewKard063" class="button icon vac-plan16"
            title="Открыть карту профилактических прививок">
             <span></span>
          </a>
        <?php } ?>
      </div>
  </div>

  <table>
    <col style="width: 10%" class="first" />
    <col style="width: 10%" />
    <col style="width: 10%" />
    <col style="width: 25%" />
    <col style="width: 25%" />
    <col style="width: 10%" />
    <col style="width: 10%" class="last" />

    <thead>
      <tr>
        <th>Статус записи</th>
        <th>Дата планирования</th>
        <th>Возраст</th>
        <th>Вид</th>
        <th>Назначение</th>
        <th>Дата начала периода планирования</th>
        <th>Дата окончания периода планирования</th>
      </tr>
    </thead>

    <tbody id="InoculationPlanList_{pid}" style="font-size: 10px;">
      {items}
    </tbody>
  </table>
</div>
