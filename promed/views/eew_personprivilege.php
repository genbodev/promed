<div id="PersonPrivilegeList_{pid}" class="data-table component read-only">
    <div class="caption">
        <h2><span id="PersonPrivilegeList_{pid}_toggleDisplay"<?php echo empty($items) ? '' : ' class="collapsible"'; ?>>Сведения об инвалидности</span></h2>
    </div>

    <table id="PersonPrivilegeTable_{pid}" style="display: <?php echo empty($items) ? 'none' : 'block'; ?>;">
        <col class="first" />
        <col />
        <col class="last" />
        <thead>
            <tr>
                <th>Тип</th>
                <th>Дата начала</th>
                <th>Дата окончания</th>
            </tr>
        </thead>
        <tbody>
            {items}
        </tbody>
    </table>

</div>