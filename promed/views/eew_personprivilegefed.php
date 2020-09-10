<div id="PersonPrivilegeFedList_{pid}" class="data-table component read-only">
    <div class="caption">
        <h2><span id="PersonPrivilegeFedList_{pid}_toggleDisplay"<?php echo empty($items) ? '' : ' class="collapsible"'; ?>>Федеральная льгота</span></h2>
    </div>

    <table id="PersonPrivilegeFedTable_{pid}" style="display: <?php echo empty($items) ? 'none' : 'block'; ?>;">
        <col class="first" />
        <col />
        <col class="last" />
        <thead>
            <tr>
                <th>Код</th>
                <th>Дата начала</th>
                <th>Дата окончания</th>
            </tr>
        </thead>
        <tbody>
            {items}
        </tbody>
    </table>

</div>