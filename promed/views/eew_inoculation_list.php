<div id="Inoculation_{pid}"
  class="data-table component read-only"
  onmouseover=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('Inoculation_{pid}_toolbar').style.display='block'"
  onmouseout=
    "if (isMouseLeaveOrEnter(event, this))
      document.getElementById('Inoculation_{pid}_toolbar').style.display='none'">

  <div class="caption">
    <h2>Исполненные прививки</h2>
    <div id="Inoculation_{pid}_toolbar" class="toolbar">
      <a id="Inoculation_{pid}_print" class="button icon icon-print16"
         title="Печать">
         <span></span>
      </a>

      <?php if (getRegionNick() == 'vologda') { ?>
        <a id="Inoculation_{pid}_viewKard063" class="button icon vac-plan16"
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

   <?php if (getRegionNick() == 'ufa') { ?>
     <col style="width: 10%" />
   <?php } ?>

    <col style="width: 10%" />
    <col style="width: 10%" />
    <col style="width: 10%" />
    <col style="width: 10%" />

    <?php if (getRegionNick() == 'vologda') { ?>
      <col style="width: 10%" />
    <?php } ?>

    <col style="width: 10%" class="last" />

    <thead>
      <tr>
        <th>Дата вакци-<br>нации</th>
        <th>Воз-<br>раст</th>
        <th>Наименова-<br>ние вакцины</th>

        <?php if (getRegionNick() == 'ufa') { ?>
          <th>Вид</th>
        <?php } ?>

        <th>Назначение</th>
        <th>Серия</th>
        <th>Доза</th>
        <th>Способ и место<br>введения</th>
        <th>Реак-<br>ция</th>

        <?php if (getRegionNick() == 'vologda') { ?>
          <th>Наименова-<br>ние ЛПУ</th>
        <?php } ?>
      </tr>
    </thead>

    <tbody id="InoculationList_{pid}" style="font-size: 10px;">
      {items}
    </tbody>
  </table>
</div>
