<div id="PersonRegisterExportList_{pid}" class="data-table component read-only">
    <div class="caption">
        <h2><span id="PersonRegisterExportList_{pid}_toggleDisplay"<?php echo empty($items) ? '' : ' class="collapsible"'; ?>>Выгрузка в федеральный регистр</span></h2>
    </div>

    <table id="PersonRegisterExportTable_{pid}" style="display: <?php echo empty($items) ? 'none' : 'block'; ?>;">
        <col class="first" />
        <col class="last" />
        <thead>
            <tr>
                <th>Дата выгрузки</th>
                <th>Тип выгрузки</th>
            </tr>
        </thead>
        <tbody>
            {items}
        </tbody>
    </table>

</div>