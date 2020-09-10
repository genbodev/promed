<div id="PersonDrugList_{pid}" class="data-table component read-only">
    <div class="caption">
        <h2><span id="PersonDrugList_{pid}_toggleDisplay"<?php echo empty($items) ? '' : ' class="collapsible"'; ?>>Лекарственные препараты</span></h2>
    </div>

    <table id="PersonDrugTable_{pid}" style="display: <?php echo empty($items) ? 'none' : 'block'; ?>;">
        <col class="first" />
        <col />
        <col />
        <col />
        <col />
        <col />
        <col class="last" />
        <thead>
            <tr>
                <th>Статус</th>
                <th>Серия / Номер</th>
                <th>МНН</th>
                <th>Торговое наименование</th>
                <th>Кол-во</th>
                <th>Дата выписки</th>
                <th>Дата отовар.</th>
            </tr>
        </thead>
        <tbody>
            {items}
        </tbody>
    </table>

</div>